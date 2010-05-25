<?php
define('TESTING',true);
require_once(PACKAGEPATH.'load.php');
class TestRunner{
	private $path;
	private $tests=array();
	public function TestRunner($path){		
		$this->path=$path;
		echo $this->path;
	}
	private function setup(){
		loadAoiSora();
		include(PACKAGEPATH.'lib/testing/Assert.php');		
		$this->loadTests($this->path);
	}
	private function loadTests($dir){
		$handle = opendir($dir);
		while(false !== ($resource = readdir($handle))) {
			if(!strstr($resource,'index'))
			if($resource!='.' && $resource!='..'){
				if(is_dir($dir.$resource))
					$this->loadTests($dir.$resource.'/');
				else{
					$this->tests[]=str_replace('.php','',$resource);
				 	include($dir.$resource);
				}
			}
		}
		closedir($handle);
	}
	function RunTests(){
		$this->setup();
		?>
	<style>
	.testcase{font-size:12pt;}
	.fail{color:red;font-weight:bold;}
	.success{color:green;font-weight:bold;}
	</style>
	<h1>TestRunner</h1>
	<?php foreach($this->tests as $test):
		$bct = new $test();
		$methods=get_class_methods($test);	
	?>

		<table>
		<caption>Testing <?php echo get_class($bct)?></caption>
		<?php foreach($methods as $method):?>
		<tr>
		<td>
		<span class="testcase"><?php echo $method;?>: </span>
		</td>
		<td>
		<?php $result=$bct->$method(); ?>
		<?php if(!$result->passed()):?>
		<span class="fail"><?php echo $result->message;?></span>
		<?php else:?>
		<span class="success">Success</span>
		<?php endif;?>
		</td>
		</tr>
		<?php endforeach;?>
		</table>
	<?php endforeach;?>
<?php 		
	}
}?>