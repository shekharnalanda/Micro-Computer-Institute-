<?php

namespace App\Support;

class StarterPracticeTests
{
    public static function all(): array
    {
        $base = [
            self::test('DCA','DCA Computer Fundamentals – Set 1',[
                ['CPU का पूरा नाम क्या है?','Central Processing Unit','Computer Processing User','Central Print Unit','Control Program Unit','A'],
                ['MS Word मुख्य रूप से किस काम के लिए है?','Video editing','Document creation','Accounting','Programming','B'],
                ['Copy का keyboard shortcut क्या है?','Ctrl+X','Ctrl+P','Ctrl+C','Ctrl+S','C'],
                ['इनमें से कौन input device है?','Monitor','Printer','Speaker','Keyboard','D'],
                ['Internet पर website खोलने के लिए क्या उपयोग होता है?','Web browser','Calculator','Paint','Notepad','A'],
            ]),
            self::test('ADCA','ADCA Advanced Applications – Set 1',[
                ['Mail Merge का उपयोग किस लिए होता है?','एक ही letter कई recipients को भेजने','Photo editing','Database delete','Video play','A'],
                ['Tally में company data किससे संबंधित है?','Gaming','Accounting','Drawing','Coding','B'],
                ['HTML का उपयोग किसके लिए होता है?','Web page structure','Antivirus scan','Payroll only','Image printing','A'],
                ['Primary key की विशेषता क्या है?','Duplicate होती है','रिक्त ही रहती है','Record को uniquely identify करती है','केवल text रखती है','C'],
                ['Presentation slide show शुरू करने की key क्या है?','F2','F5','F8','F12','B'],
            ]),
            self::test('CCC','CCC Digital Literacy – Set 1',[
                ['OTP किसके साथ share करना सुरक्षित है?','Bank caller','Friend','किसी के साथ नहीं','Shopkeeper','C'],
                ['Email address में सामान्यतः कौन-सा चिन्ह होता है?','#','@','%','&','B'],
                ['UPI का उपयोग किस लिए होता है?','Digital payment','Photo editing','Typing','Printing','A'],
                ['Strong password में क्या होना चाहिए?','केवल नाम','केवल 12345','अक्षर, अंक और symbols','जन्मतिथि','C'],
                ['Cloud storage का उदाहरण कौन है?','Google Drive','Calculator','Notepad','Paint','A'],
            ]),
            self::test('TALLY','Tally Prime & GST – Set 1',[
                ['Accounting equation क्या है?','Assets = Liabilities + Capital','Sales = Purchase + Stock','Cash = Profit + Loss','GST = Income + Expense','A'],
                ['GST का पूरा नाम क्या है?','General Sales Tax','Goods and Services Tax','Government Service Tariff','Gross Service Total','B'],
                ['Purchase transaction दर्ज करने के लिए कौन-सा voucher है?','Receipt','Contra','Purchase','Payment','C'],
                ['Customer account सामान्यतः किस group में आता है?','Sundry Debtors','Fixed Assets','Capital Account','Indirect Expenses','A'],
                ['Trial Balance का उद्देश्य क्या है?','Logo बनाना','Debit और Credit totals जाँचना','Email भेजना','Stock print करना','B'],
            ]),
            self::test('EXCEL','Advanced Excel & MIS – Set 1',[
                ['Formula किस चिन्ह से शुरू होता है?','@','=','&','#','B'],
                ['SUM function क्या करता है?','Text delete','Values जोड़ता है','Sheet lock','Chart हटाता है','B'],
                ['Absolute cell reference का उदाहरण क्या है?','A1','$A$1','A:A','1A','B'],
                ['PivotTable किस लिए उपयोगी है?','Data summary और analysis','Typing speed','Photo crop','Email login','A'],
                ['VLOOKUP में lookup value कहाँ खोजी जाती है?','Table की पहली column','अंतिम row','Chart title','Footer','A'],
            ]),
            self::test('DTP','DTP & Graphic Design – Set 1',[
                ['CMYK color mode मुख्यतः कहाँ उपयोग होता है?','Printing','Audio','Database','Coding','A'],
                ['Photoshop में layers का लाभ क्या है?','Elements को अलग-अलग edit करना','Internet तेज करना','File rename','Sound record','A'],
                ['Vector graphics की विशेषता क्या है?','Resize पर quality बनी रहती है','केवल black होती है','Sound रखती है','Formula चलाती है','A'],
                ['Brochure में bleed क्यों दिया जाता है?','Cutting के बाद white edge से बचने','Password लगाने','Email भेजने','Font delete करने','A'],
                ['CorelDRAW किस प्रकार का software है?','Vector design','Accounting','Antivirus','Spreadsheet','A'],
            ]),
            self::test('WEB','Web Design & Development – Set 1',[
                ['HTML का पूरा नाम क्या है?','HyperText Markup Language','HighText Machine Language','Home Tool Markup Language','Hyper Transfer Main Link','A'],
                ['CSS का उपयोग किस लिए होता है?','Page styling','Database backup','Email hosting','Virus scan','A'],
                ['JavaScript क्या जोड़ता है?','Interactivity','Printer ink','Domain expiry','File compression only','A'],
                ['Responsive design का उद्देश्य क्या है?','अलग screen sizes पर सही layout','केवल desktop support','Internet बंद करना','Password हटाना','A'],
                ['Secure website URL सामान्यतः किससे शुरू होता है?','ftp://','file://','https://','mail://','C'],
            ]),
            self::test('PYTHON','Python Programming – Set 1',[
                ['Python में output दिखाने के लिए कौन-सा function है?','echo()','print()','show()','writehtml()','B'],
                ['List किस brackets में लिखी जाती है?','[]','{}','()','<>','A'],
                ['Condition के लिए कौन-सा keyword है?','loop','when','if','check','C'],
                ['len() function क्या देता है?','Length','Color','File name','Password','A'],
                ['Python में comment किससे शुरू होता है?','//','#','<!--','**','B'],
            ]),
            self::test('DIGITAL','Digital Marketing – Set 1',[
                ['SEO का मुख्य उद्देश्य क्या है?','Search visibility बढ़ाना','Computer format करना','Logo print करना','Payroll बनाना','A'],
                ['CTR का अर्थ क्या है?','Click Through Rate','Content Total Reach','Customer Time Record','Campaign Tax Report','A'],
                ['Organic traffic कहाँ से आता है?','Unpaid search results','केवल paid ads','Offline printer','USB drive','A'],
                ['Social media content calendar किस लिए है?','Posts plan और schedule करने','Password store करने','GST return','Code compile','A'],
                ['Google Analytics क्या मापता है?','Website traffic और behavior','Typing speed','Printer quality','Electricity','A'],
            ]),
            self::test('HARDWARE','Hardware & Networking – Set 1',[
                ['RAM कैसी memory है?','Volatile','Permanent optical','Paper','Mechanical only','A'],
                ['LAN का पूरा नाम क्या है?','Local Area Network','Large Access Node','Linked Account Number','Long Area Name','A'],
                ['Router का मुख्य काम क्या है?','Networks के बीच data route करना','Document type करना','Image crop करना','Audio record करना','A'],
                ['SSD की तुलना में HDD में सामान्यतः क्या होता है?','Moving parts','No storage','Only RAM','No file system','A'],
                ['IP address किसकी पहचान करता है?','Network device','Printed page','Keyboard key','Folder color','A'],
            ]),
            self::test('AI','AI Tools for Study & Work – Set 1',[
                ['AI prompt क्या है?','AI को दिया गया निर्देश या प्रश्न','Computer cable','Printer paper','Bank voucher','A'],
                ['AI output उपयोग करने से पहले क्या करना चाहिए?','Accuracy verify करनी चाहिए','बिना पढ़े publish','Password देना','OTP share करना','A'],
                ['Sensitive data AI tool में डालना कैसा है?','Avoid करना चाहिए','हमेशा जरूरी','Public करना चाहिए','कोई फर्क नहीं','A'],
                ['Generative AI क्या बना सकता है?','Text और images','केवल keyboard','केवल बिजली','Physical RAM','A'],
                ['Responsible AI use में क्या शामिल है?','Privacy, verification और attribution','Fake information फैलाना','Copyright ignore करना','Passwords share करना','A'],
            ]),
            self::test('DATA','Data Entry & Office Assistant – Set 1',[
                ['Numeric keypad किस काम में मदद करता है?','तेज number entry','Photo editing','Network routing','Video rendering','A'],
                ['Ctrl+S का उपयोग क्या है?','Save','Search only','Shutdown','Select printer','A'],
                ['Data validation क्यों उपयोग होती है?','गलत entry सीमित करने','Font बड़ा करने','Internet connect','Audio play','A'],
                ['Official email में subject कैसा होना चाहिए?','स्पष्ट और संक्षिप्त','खाली','केवल emoji','बहुत अस्पष्ट','A'],
                ['Spreadsheet में row और column के intersection को क्या कहते हैं?','Cell','Slide','Layer','Voucher','A'],
            ]),
        ];

        $sets=[];
        foreach($base as $test){
            $bank=array_merge($test['questions'],self::extraQuestions($test['course_code']));
            foreach([
                [1,'practice','Practice Test 1',10,15,40],
                [2,'practice','Practice Test 2',10,15,40],
                [3,'terminal','Terminal Test 1',20,25,40],
                [4,'terminal','Terminal Test 2',20,25,40],
                [5,'final','Final Test',40,40,40],
            ] as [$setNo,$type,$label,$weight,$minutes,$pass]){
                $copy=$test;
                $copy['starter_key']=strtolower($test['course_code']).'-set-'.$setNo;
                $copy['title']=$test['course_code'].' '.$label;
                $copy['assessment_type']=$type;
                $copy['assessment_order']=$setNo;
                $copy['assessment_weight']=$weight;
                $copy['duration_minutes']=$minutes;
                $copy['pass_percentage']=$pass;
                $indexes=match($setNo){1=>[0,1,2,3,4],2=>[5,6,7,8,9],3=>[0,2,4,6,8],4=>[1,3,5,7,9],default=>range(0,9)};
                $copy['questions']=array_values(array_map(function(int $bankIndex,int $index)use($test,$setNo,$bank):array{
                    $question=$bank[$bankIndex];
                    $question['id']='starter-'.strtolower($test['course_code']).'-s'.$setNo.'-q'.($index+1);
                    return $question;
                },$indexes,array_keys($indexes)));
                $sets[]=$copy;
            }
        }
        return $sets;
    }

    private static function extraQuestions(string $course): array
    {
        $items=[
            'DCA'=>[
                ['Spreadsheet में formula किस चिन्ह से शुरू होता है?','=','@','#','&','A'],['Database में एक row को क्या कहते हैं?','Field','Record','Query','Form','B'],['Mail Merge में variable data कहाँ रहता है?','Data source','Printer','Slide master','Browser cache','A'],['Presentation शुरू करने की सामान्य key कौन है?','F1','F3','F5','F9','C'],['Backup का मुख्य उद्देश्य क्या है?','Font बदलना','Data recovery','Internet speed','Screen brightness','B']],
            'ADCA'=>[
                ['Foreign key का काम क्या है?','Tables को relate करना','Font lock करना','File compress करना','Slide चलाना','A'],['Excel PivotTable किसलिए है?','Data summary','Photo crop','Virus scan','Email send','A'],['GST की local sale में सामान्यतः कौन-से tax लगते हैं?','केवल IGST','CGST और SGST','कोई tax नहीं','Custom duty','B'],['Algorithm क्या है?','Step-by-step solution','Hardware part','Image format','Accounting ledger','A'],['Project testing में invalid input क्यों जाँचा जाता है?','Error handling verify करने','Colour बदलने','File size बढ़ाने','Password share करने','A']],
            'CCC'=>[
                ['Operating system का उदाहरण कौन है?','Windows','Excel formula','JPEG','Keyboard','A'],['Ctrl+Z क्या करता है?','Undo','Print','Paste','Close','A'],['Phishing का उद्देश्य अक्सर क्या होता है?','Sensitive information चुराना','Typing सिखाना','Printer repair','Storage बढ़ाना','A'],['PDF का लाभ क्या है?','Layout share करने में स्थिर रहता है','केवल audio रखता है','Internet बनाता है','RAM बढ़ाता है','A'],['MFA किसे मजबूत करता है?','Account security','Screen colour','Printer speed','File name','A']],
            'TALLY'=>[
                ['Cash bank transfer के लिए कौन voucher है?','Sales','Contra','Purchase','Journal only','B'],['Profit and Loss report क्या दिखाती है?','Income और expenses','केवल passwords','Network devices','Page margins','A'],['Interstate taxable sale में सामान्यतः कौन tax है?','IGST','केवल SGST','केवल CGST','कोई नहीं','A'],['Stock item के लिए unit का उदाहरण क्या है?','Nos','Ledger','Voucher','Capital','A'],['Backup restore से पहले क्या जाँचना चाहिए?','सही company और backup file','Font colour','Mouse pad','Email subject','A']],
            'EXCEL'=>[
                ['COUNTIFS क्या करता है?','कई conditions पर count','Workbook delete','Chart print','Text translate','A'],['XLOOKUP का not_found argument किसलिए है?','Missing match का message','Sheet password','Colour scale','Macro recording','A'],['Data Validation list क्या बनाती है?','Dropdown','Pivot chart','New workbook','PDF','A'],['Power Query मुख्यतः किसलिए है?','Data import और transformation','Slide show','Photo painting','Email hosting','A'],['Control total का उद्देश्य क्या है?','Report reconciliation','Font selection','Internet login','Page break','A']],
            'DTP'=>[
                ['Raster image किससे बनी होती है?','Pixels','Ledgers','Cells formulas','Vouchers','A'],['Typography में leading क्या है?','Lines के बीच spacing','Page colour','Image crop','File password','A'],['Print design में safe margin क्यों है?','Text कटने से बचाने','Audio बढ़ाने','Internet जोड़ने','Layer merge करने','A'],['Logo के लिए कौन format scalable हो सकता है?','SVG','BMP only','TXT','MP3','A'],['Preflight कब किया जाता है?','Final print export से पहले','Typing शुरू होने से पहले ही केवल','Email login पर','Computer shutdown पर','A']],
            'WEB'=>[
                ['Semantic navigation के लिए कौन element है?','nav','bold','photo','database','A'],['CSS box model में क्या शामिल है?','Content padding border margin','केवल font','केवल image','केवल script','A'],['DOM क्या दर्शाता है?','Page document structure','Domain payment','Database password','Design output mode','A'],['Server-side validation क्यों जरूरी है?','Client checks bypass हो सकते हैं','Colour बदलने','Image resize','SEO title','A'],['.env file में सामान्यतः क्या रखा जाता है?','Environment secrets/config','Public logo','Article text','CSS colour only','A']],
            'PYTHON'=>[
                ['Dictionary किस प्रकार data रखती है?','Key-value pairs','केवल pixels','Slides','Rows without keys','A'],['range(5) में अंतिम value क्या होगी?','4','5','6','0 only','A'],['Function value वापस करने के लिए keyword क्या है?','return','print','class','input','A'],['File safely बंद करने के लिए कौन pattern उपयोगी है?','with open','while true','global only','break file','A'],['Exception handling के keywords कौन हैं?','try और except','if और else only','for और range','def और return','A']],
            'DIGITAL'=>[
                ['CPL का पूरा अर्थ क्या है?','Cost Per Lead','Click Page Limit','Customer Post Link','Campaign Profit Loss','A'],['CTA क्या है?','Call To Action','Content Traffic Audit','Click Time Average','Customer Tax Account','A'],['UTM parameter किसलिए है?','Campaign source tracking','Image editing','GST calculation','Hardware test','A'],['Local SEO में कौन उपयोगी है?','Accurate business profile','Hidden address','Keyword stuffing','Fake reviews','A'],['A/B test में मुख्यतः क्या बदलना चाहिए?','एक major variable','सब कुछ एक साथ','कोई measurement नहीं','Password','A']],
            'HARDWARE'=>[
                ['POST कब होता है?','Computer startup पर','Document print पर','Email send पर','Spreadsheet save पर','A'],['DNS का काम क्या है?','Name को IP से resolve करना','RAM बढ़ाना','Image crop','Voucher create','A'],['DHCP क्या देता है?','Automatic network settings','Printer ink','CPU speed','File password','A'],['ESD से बचाव क्यों जरूरी है?','Components damage से बचाने','Internet speed','Typing accuracy','GST return','A'],['ping command किसे test करती है?','Network reachability','Font size','Disk formatting only','Invoice tax','A']],
            'AI'=>[
                ['AI hallucination क्या है?','विश्वसनीय दिखने वाली गलत जानकारी','Hardware failure','Fast internet','Encrypted backup','A'],['Primary source का उदाहरण क्या है?','Official notification','Unknown forwarded message','Random comment','Unverified screenshot','A'],['Prompt में constraint क्यों दिया जाता है?','Output सीमा स्पष्ट करने','Password बताने','OTP लेने','Bias बढ़ाने','A'],['AI bias का अर्थ क्या है?','अनुचित झुकाव या असमान परिणाम','Backup copy','File format','Typing speed','A'],['Human review की जिम्मेदारी किसकी रहती है?','Output उपयोग करने वाले व्यक्ति की','केवल AI की','किसी की नहीं','Printer की','A']],
            'DATA'=>[
                ['Unique ID क्यों उपयोग होता है?','Record पहचानने','Font बदलने','Internet तेज करने','Slide बनाने','A'],['TRIM function क्या हटाती है?','Extra spaces','Formulas','Charts','Passwords','A'],['Mail subject कैसा होना चाहिए?','Specific और concise','Blank','केवल emoji','भ्रामक','A'],['Double-entry verification का उद्देश्य क्या है?','Data accuracy जाँचना','Accounting voucher बनाना','Image layer','Network route','A'],['Document version में final शब्द कब लगाना चाहिए?','Approval के बाद','पहले draft पर','हर copy पर','बिना review','A']],
        ];
        return array_map(fn(array $q,int $i):array=>[
            'id'=>'starter-'.strtolower($course).'-extra-q'.($i+1),'prompt'=>$q[0],
            'options'=>['A'=>$q[1],'B'=>$q[2],'C'=>$q[3],'D'=>$q[4]],'correct'=>$q[5],
        ],$items[$course]??[],array_keys($items[$course]??[]));
    }

    private static function test(string $course,string $title,array $questions): array
    {
        return [
            'starter_key'=>strtolower($course).'-set-1','course_code'=>$course,'title'=>$title,
            'duration_minutes'=>15,'pass_percentage'=>40,
            'questions'=>array_map(fn(array $q,int $i):array=>[
                'id'=>'starter-'.strtolower($course).'-q'.($i+1),'prompt'=>$q[0],
                'options'=>['A'=>$q[1],'B'=>$q[2],'C'=>$q[3],'D'=>$q[4]],'correct'=>$q[5],
            ],$questions,array_keys($questions)),
        ];
    }
}
