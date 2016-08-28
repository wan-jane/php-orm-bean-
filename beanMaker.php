<?php 
$opt = getopt("t:");
$tableName = '';
if (isset($opt['t']) && $opt['t']) {
    $tableName = $opt['t'];
} else {
    die("请加参数：-t=tablename\n");
}


// 数据库配置参数
$db_config = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'username' => 'root',
    'password' => 'root',
    'dbname' => 'xnw',
    'charset' => 'utf8'
);

$res = linkdb($db_config, $tableName);

$className = getCamelName($tableName) . 'Bean';
$fileName = $className . '.php';
$annStr = addAnnotation($fileName, $tableName, 'javabean');
$classStr = readTableStr($res, $className, $tableName,$annStr);
// 写入文件
$file = fopen($fileName, "w+");
fwrite($file, $classStr);
fclose($file);
/**
 * 根据表结构初始化表对象
 *
 * @param unknown $res            
 * @param unknown $className            
 * @return string
 */
function readTableStr($res, $className, $table, $annStr='')
{
    $result = '<?php ' . "\n";
    $result .= $annStr;
    $result .= "class $className extends Model{\n";
    $result .= "\n    protected \$table = '{$table}'; //表名";
    
    // 变量
    foreach ($res as $v) {
        if (is_int($v['Default'])) {
            $result .= "\n" . '    private $' . $v['Field'] . " = {$v['Default']}; //" . $v['Comment'] . $v['Type'];
        } else {
            $result .= "\n" . '    private $' . $v['Field'] . " = '{$v['Default']}'; //" . $v['Comment'] . $v['Type'];
        }
    }
    $result .= "\n";
    // get set
    foreach ($res as $v) {
        $result .= "\n     public function set" . getCamelName($v['Field']) . '($' . $v['Field'] . ") {";
        $result .= "\n" . '        $this->' . $v['Field'] . ' = $' . $v['Field'] . ";";
        $result .= "\n     }";
        $result .= "\n     public function get" . getCamelName($v['Field']) . '() {';
        $result .= "\n" . '        return $this->' . $v['Field'] . ";";
        $result .= "\n     }";
        $result .= "\n";
    }
    //生成toArray方法
    $result .= "\n     public function toArray() {";
    $result .= "\n         return [";
    foreach ($res as $v) {
        $result .= "\n             '{$v['Field']}' => " . "\$this->{$v['Field']},";
    }
    $result .= "\n         ];";
    $result .= "\n     }";
    
    //生成toString方法
    $result .= "\n     public function __toString() {";
    $result .= "\n         return json_encode(\$this->toArray(), JSON_UNESCAPED_UNICODE);";
    $result .= "\n     }";
    
    //类结束
    $result .= " \n }";
    $result .= " \n";
    return $result;
}
/**
 * 添加类注释
 * 
 * @param unknown $fileName            
 * @param unknown $fun            
 * @param unknown $des            
 * @param unknown $date            
 * @param unknown $author            
 */
function addAnnotation($fileName, $fun, $des)
{
    $annStr =  "\n/**";
    $annStr .= "\n* 文件名：" . $fileName;
    $annStr .= "\n* 功能：    模型层-表-" . $fun;
    $annStr .= "\n* 描述：    " . $des;
    $annStr .= "\n* 日期：    " . date('y-m-d', time());
    $annStr .= "\n*/\n";
    return $annStr;
}
/**
 * * 连接数据库，查询表结构
 *
 * @param unknown $array
 *            数据库连接参数
 * @param unknown $tableName
 *            表名
 */
function linkdb($array, $tableName)
{
    $dsn = "mysql:host={$array['host']};dbname={$array['dbname']};charset={$array['charset']}";
    $username =  $array['username'];
    $password = $array['password'];

    $dbh = new PDO($dsn, $username, $password);
    
    $sql = "SHOW FULL COLUMNS FROM $tableName";
    $st = $dbh->query($sql);
    $res = $st->fetchAll(PDO::FETCH_ASSOC);
    return $res;
}

function getCamelName($r) {
    $temp = explode('_', $r);
    $tmp = '';
    foreach ($temp as $v) {
        $tmp .= ucfirst($v);
    }
    return $tmp;
}
/**
 * 调试函数
 *
 * @param string $msg            
 */
function show_bug($msg)
{
    echo '<pre>';
    var_dump($msg);
    echo '</pre>';
}

