<?php
namespace meCab;

/**
 * Class meCab
 * @package meCab
 */
class meCab{
    private $tmp_file;
    private $dictionary;

    static private $dictionary_dir;
    static private $class_inited;

    function __construct()
    {
        $this->tmp_file = tempnam(sys_get_temp_dir(),'mecab');
        if(!self::$class_inited){
            self::autoDictionaryDir();
        }
    }

    /**
     * @param string $dictionary_dir
     */
    static public function setDictionaryDir($dictionary_dir)
    {
        self::$dictionary_dir = $dictionary_dir;
        self::$class_inited = true;
    }

    /**
     * @return string
     */
    static public function getDictionaryDir()
    {
        return self::$dictionary_dir;
    }

    /**
     * @return string
     */
    static public function autoDictionaryDir(){
        self::$dictionary_dir = exec('echo `mecab-config --dicdir`',$res);
        self::$class_inited = true;
        return self::$dictionary_dir;
    }

    /**
     * @param $dictionary
     */
    public function setDictionary($dictionary){
        $path = self::$dictionary_dir.$dictionary;
        if(!file_exists($path)){
            throw new \Exception(sprintf('Not Found dictionary In "%s"',$dictionary));
        }
        $this->dictionary = $dictionary;
    }

    /**
     * @param $text
     * @return meCabWord[]|null
     * @throws \Exception
     */
    public function analysis($text){
        if(file_put_contents($this->tmp_file,$text)){
            $command = array('mecab');
            if($this->dictionary){
                $command[] = '-d '.self::$dictionary_dir.$this->dictionary;
            }
            $this->exec(implode(' ',$command).' '.$this->tmp_file,$res);
            if($res && (count($res) > 0)){
                if(preg_match('/ /',$res[0])){
                    throw new \Exception($res[0]);
                }
                $words = array();
                foreach($res as $k => $r){
                    if($r == 'EOS' && count($res) >= ($k + 1)){
                        break;
                    }
                    $words[] = new meCabWord($r);
                }
                return $words;
            }else{
                throw new \Exception(sprintf('Error text analysis.'));
            }
        }else{
            throw new \Exception(sprintf('Error write tmp file in %s',$this->tmp_file));
        }
    }

    /**
     * @param $command
     * @param $res
     * @return string
     */
    private function exec($command,&$res){
        if($text = exec($command,$res)){
        }
        return $text;
    }
}

/**
 * Class meCabWord
 * @package meCab
 */
class meCabWord{
    protected $str;
    protected $text;
    protected $speech;
    protected $speech_info;
    protected $conjugate;
    protected $conjugate_type;
    protected $original;
    protected $reading;
    protected $pronunciation;

    #TODO type static
    /*
     その他,間投,*,* 0
フィラー,*,*,* 1
感動詞,*,*,* 2
記号,アルファベット,*,* 3
記号,一般,*,* 4
記号,括弧開,*,* 5
記号,括弧閉,*,* 6
記号,句点,*,* 7
記号,空白,*,* 8
記号,読点,*,* 9
形容詞,自立,*,* 10
形容詞,接尾,*,* 11
形容詞,非自立,*,* 12
助詞,格助詞,一般,* 13
助詞,格助詞,引用,* 14
助詞,格助詞,連語,* 15
助詞,係助詞,*,* 16
助詞,終助詞,*,* 17
助詞,接続助詞,*,* 18
助詞,特殊,*,* 19
助詞,副詞化,*,* 20
助詞,副助詞,*,* 21
助詞,副助詞／並立助詞／終助詞,*,* 22
助詞,並立助詞,*,* 23
助詞,連体化,*,* 24
助動詞,*,*,* 25
接続詞,*,*,* 26
接頭詞,形容詞接続,*,* 27
接頭詞,数接続,*,* 28
接頭詞,動詞接続,*,* 29
接頭詞,名詞接続,*,* 30
動詞,自立,*,* 31
動詞,接尾,*,* 32
動詞,非自立,*,* 33
副詞,一般,*,* 34
副詞,助詞類接続,*,* 35
名詞,サ変接続,*,* 36
名詞,ナイ形容詞語幹,*,* 37
名詞,一般,*,* 38
名詞,引用文字列,*,* 39
名詞,形容動詞語幹,*,* 40
名詞,固有名詞,一般,* 41
名詞,固有名詞,人名,一般 42
名詞,固有名詞,人名,姓 43
名詞,固有名詞,人名,名 44
名詞,固有名詞,組織,* 45
名詞,固有名詞,地域,一般 46
名詞,固有名詞,地域,国 47
名詞,数,*,* 48
名詞,接続詞的,*,* 49
名詞,接尾,サ変接続,* 50
名詞,接尾,一般,* 51
名詞,接尾,形容動詞語幹,* 52
名詞,接尾,助数詞,* 53
名詞,接尾,助動詞語幹,* 54
名詞,接尾,人名,* 55
名詞,接尾,地域,* 56
名詞,接尾,特殊,* 57
名詞,接尾,副詞可能,* 58
名詞,代名詞,一般,* 59
名詞,代名詞,縮約,* 60
名詞,動詞非自立的,*,* 61
名詞,特殊,助動詞語幹,* 62
名詞,非自立,一般,* 63
名詞,非自立,形容動詞語幹,* 64
名詞,非自立,助動詞語幹,* 65
名詞,非自立,副詞可能,* 66
名詞,副詞可能,*,* 67
連体詞,*,*,* 68
     */
    /**
     * @param $text
     */
    function __construct($text)
    {
        $this->str = $text;

        $res = preg_split('/\t/',$text);
        if(count($res) == 2){
            $this->text = $res[0];
            $info = explode(',',$res[1]);

            $this->speech_info = array_fill(0,3,null);
            foreach($info as $k => $t){
                if($t == '*'){
                    continue;
                }
                if($k == 0){
                    $this->speech = $t;
                }else if($k <= 3){
                    $this->speech_info[$k - 1] = $t;
                }else if($k == 4){
                    $this->conjugate = $t;
                }else if($k == 5){
                    $this->conjugate_type = $t;
                }else if($k == 6){
                    $this->original = $t;
                }else if($k == 7){
                    $this->reading = $t;
                }else if($k == 8){
                    $this->pronunciation = $t;
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getStr()
    {
        return $this->str;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getSpeech()
    {
        return $this->speech;
    }

    /**
     * @return mixed
     */
    public function getSpeechInfo()
    {
        return $this->speech_info;
    }

    /**
     * @return mixed
     */
    public function getConjugate()
    {
        return $this->conjugate;
    }

    /**
     * @return mixed
     */
    public function getConjugateType()
    {
        return $this->conjugate_type;
    }

    /**
     * @return mixed
     */
    public function getPronunciation()
    {
        return $this->pronunciation;
    }

    /**
     * @return mixed
     */
    public function getReading()
    {
        return $this->reading;
    }

    /**
     * @return mixed
     */
    public function getOriginal()
    {
        return $this->original;
    }

}