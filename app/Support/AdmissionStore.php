<?php

namespace App\Support;

use Illuminate\Support\Str;

class AdmissionStore
{
    private static function path(): string
    {
        return storage_path('app/mci-admissions.json');
    }

    public static function all(): array
    {
        $items = is_readable(self::path()) ? json_decode((string) file_get_contents(self::path()), true) : [];
        $items = is_array($items) ? array_values($items) : [];
        usort($items, fn (array $a, array $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

        return $items;
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $item) {
            if (($item['id'] ?? '') === $id) return $item;
        }

        return null;
    }

    public static function add(array $data): array
    {
        $items = self::all();
        $courseFee = (float) ($data['course_fee'] ?? 0);
        $item = array_merge($data, [
            'id' => (string) Str::uuid(),
            'application_no' => 'MCI-'.now()->format('ymd').'-'.strtoupper(Str::random(5)),
            'status' => 'pending',
            'course_fee' => $courseFee,
            'paid_amount' => 0,
            'balance_amount' => $courseFee,
            'payment_status' => 'unpaid',
            'receipt_no' => null,
            'payments' => [],
            'created_at' => now()->toIso8601String(),
        ]);
        array_unshift($items, $item);
        self::write($items);

        return $item;
    }

    public static function updateStatus(string $id, string $status): bool
    {
        return self::update($id, function (array $item) use ($status): array {
            $item['status'] = $status;
            return $item;
        });
    }

    public static function updatePayment(string $id, float $courseFee, float $paidAmount, ?string $paymentNote = null): bool
    {
        return self::update($id, function (array $item) use ($courseFee, $paidAmount, $paymentNote): array {
            $balance = max(0, $courseFee - $paidAmount);
            $item['course_fee'] = $courseFee;
            $item['paid_amount'] = $paidAmount;
            $item['balance_amount'] = $balance;
            $item['payment_status'] = $paidAmount <= 0 ? 'unpaid' : ($balance > 0 ? 'partial' : 'paid');
            $item['payment_note'] = $paymentNote;
            if ($paidAmount > 0 && empty($item['receipt_no'])) {
                $item['receipt_no'] = 'MCI-R'.now()->format('ymd').'-'.strtoupper(Str::random(4));
            }
            return $item;
        });
    }

    public static function addPaymentTransaction(string $id, float $amount, string $date, string $mode, ?string $reference = null, ?string $note = null): bool
    {
        return self::update($id, function (array $item) use ($amount, $date, $mode, $reference, $note): array {
            $receiptNo = 'MCI-R'.now()->format('ymd').'-'.strtoupper(Str::random(5));
            $payments = is_array($item['payments'] ?? null) ? $item['payments'] : [];
            $payments[] = [
                'id' => (string) Str::uuid(),
                'receipt_no' => $receiptNo,
                'payment_date' => $date,
                'amount' => $amount,
                'mode' => $mode,
                'reference' => $reference,
                'note' => $note,
                'created_at' => now()->toIso8601String(),
            ];
            $courseFee = (float) ($item['course_fee'] ?? 0);
            $paidAmount = (float) ($item['paid_amount'] ?? 0) + $amount;
            $balance = max(0, $courseFee - $paidAmount);
            $item['payments'] = $payments;
            $item['paid_amount'] = $paidAmount;
            $item['balance_amount'] = $balance;
            $item['payment_status'] = $balance <= 0 ? 'paid' : 'partial';
            $item['receipt_no'] = $receiptNo;
            return $item;
        });
    }

    public static function updateStudentRecord(string $id, array $data): bool
    {
        return self::update($id, function (array $item) use ($data): array {
            $item['roll_no'] = $data['roll_no'] ?? null;
            $item['batch_name'] = $data['batch_name'] ?? null;
            $item['batch_time'] = $data['batch_time'] ?? null;
            $item['joining_date'] = $data['joining_date'] ?? null;
            $item['student_status'] = $data['student_status'] ?? 'active';
            if (! empty($data['photo_path'])) $item['photo_path'] = $data['photo_path'];
            return $item;
        });
    }

    public static function remove(string $id): bool
    {
        $items = self::all();
        $before = count($items);
        $items = array_values(array_filter($items, fn (array $item) => ($item['id'] ?? '') !== $id));
        if (count($items) === $before) return false;
        self::write($items);

        return true;
    }

    public static function removeDemoData(): array
    {
        $items=self::all();
        $ids=array_values(array_column(array_filter($items,fn(array $item):bool=>(bool)($item['is_demo']??false)),'id'));
        if($ids) self::write(array_values(array_filter($items,fn(array $item):bool=>!in_array($item['id']??'',$ids,true))));
        return $ids;
    }

    private static function update(string $id, callable $callback): bool
    {
        $items = self::all();
        $changed = false;
        foreach ($items as &$item) {
            if (($item['id'] ?? '') === $id) {
                $item = $callback($item);
                $item['updated_at'] = now()->toIso8601String();
                $changed = true;
                break;
            }
        }
        unset($item);
        if ($changed) self::write($items);

        return $changed;
    }

    private static function write(array $items): void
    {
        file_put_contents(self::path(), json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
