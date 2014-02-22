<?php
require_once("class.Quartz.php");


class TEMPLATE{

	public static function htmlWrap( $callback, $arguments = array() ){
		ob_start();
		include('_header.php');
		print( call_user_func_array($callback, $arguments) );
		include('_footer.php');
		return ob_get_clean();
	}

	public static function install_s1( $params = array() ){
		ob_start(); ?>
		<div class="wrapper">
			<div class="section install">
				<h2>Quartz Installation Wizard</h2>

				<form method="post" action="/install.php?step=2">
				<table class="g50 center">
					<tr class="error error1" style="<?=isset($params['errors'])&&isset($params['errors']['error1'])?'display:table-row':''?>">
						<td colspan="2"><?=isset($params['errors'])&&isset($params['errors']['error1'])?$params['errors']['error1']:''?></td>
					</tr>
					<tr>
						<td><input type="text" name="host" placeholder="Host" value="<?=isset($params['host'])?$params['host']:'localhost'?>"></td>
						<td>The host on which your MySQL server is located. <i>eg. localhost</i></td>
					</tr>
					<tr>
						<td><input type="text" name="database" placeholder="Database Name" value="<?=isset($params['database'])?$params['database']:'quartz'?>"></td>
						<td>The name of your MySQL database. <i>eg. quartz</i></td>
					</tr>
					<tr>
						<td><input type="text" name="username" placeholder="Username" value="<?=isset($params['username'])?$params['username']:'username'?>"></td>
						<td>The username for your database. <i>eg. root</i></td>
					</tr>
					<tr>
						<td><input type="text" name="password" placeholder="Password" value="<?=isset($params['password'])?$params['password']:'password'?>"></td>
						<td>The password for your username. <i>eg. root</i></td>
					</tr>
					<tr>
						<td colspan="2"><input type="submit" value="Connect to MySQL"></td>
					</tr>
				</table>
				</form>
			</div>
		</div>
<?		return ob_get_clean();
	}

	public static function configFile( $params = array() ){
		ob_start();
		?>

		// Host MySQL is located on
		define('MySQL_HOST', '<?=addslashes(isset($params['host'])?$params['host']:'localhost')?>');

		// MySQL DB
		define('MySQL_DB', '<?=addslashes(isset($params['database'])?$params['database']:'quartz')?>');

		// MySQL User
		define('MySQL_USER', '<?=addslashes(isset($params['username'])?$params['username']:'username')?>');

		// Password for MySQL User
		define('MySQL_PASSWORD', '<?=addslashes(isset($params['password'])?$params['password']:'password')?>');

<?		return "<?".ob_get_clean()."?>";
	}


	public static function install_s2_denied( $params = array() ){
		ob_start(); ?>
		<div class="wrapper">
			<div class="section install">
				<h2>Quartz Installation Wizard Step 2</h2>
				<div class="g60 center">
					<p>Permission was denied trying to create <b>config.php</b> in the Quartz directory. Copy and paste the code below into a plain text editor and save it as <b>config.php</b> in the Quartz folder.</p>
					<br>
					<?=$params['html']?>
					<form class="step2" method="post" action="/install.php?step=3">
						<input type="hidden" name="configReady" value="1">
						<input type="submit" value="Config File is Made">
					</form>
				</div>
			</div>
		</div>
<?		return ob_get_clean();
	}


}

// Get Config
$config = Quartz::getConfig();

// Scope Test!
print(MySQL_DB);


// Get Step
$step = isset($_GET['step']) && is_numeric($_GET['step']) && $_GET['step']>1 ? intval($_GET['step']) : 1 ;

// Step 1
if( $step === 1 ){
	print( TEMPLATE::htmlWrap( "TEMPLATE::install_s1" ) );
}else

// Step 2
if(
	$step === 2 &&

	// Check If Input Exists
	isset( $_POST['host'] ) && strlen( $_POST['host'] )>0 &&
	isset( $_POST['username'] ) && strlen( $_POST['username'] )>0 &&
	isset( $_POST['password'] ) && strlen( $_POST['password'] )>0 &&
	isset( $_POST['database'] ) && strlen( $_POST['database'] )>0

){

	// Try MySQL Connection
	$mysqli = @new mysqli($_POST['host'], $_POST['username'], $_POST['password'], $_POST['database']);

	// If Error
	if( $mysqli->connect_error ){

		// Error Message
		$error =	array(
						'errors'	=>	array(
											'error1' => 'Error: Could not establish MySQL connection. Please verify your credentials.'
										),
						'host'		=> htmlentities($_POST['host']),
						'username'	=> htmlentities($_POST['username']),
						'password'	=> htmlentities($_POST['password']),
						'database'	=> htmlentities($_POST['database'])
					);

		// Return Error
		print( isset($_POST['ajax']) ? json_encode($error) : TEMPLATE::htmlWrap( "TEMPLATE::install_s1", array($error) ) );

	}else{

		// Success!

		// Check If Writable
		if ( is_writable( $config['path'] . $config['file'] ) ){
			$config = fopen( $config['path'] . $config['file'], "a" );	

			// Create File


			// Verify in Step 3

		}else{
			//Permissions Denied

			$return = array(
				'html' => '<textarea class="code">' . htmlentities( TEMPLATE::configFile() ) . '</textarea>'
			);

			// Show Step 2 - Permissions Denied
			print( isset($_POST['ajax']) ? json_encode(array('jQ' => array('replaceWith' => '.wrapper'), 'html' => TEMPLATE::install_s2_denied($return))) : TEMPLATE::htmlWrap("TEMPLATE::install_s2_denied", array($return)) );
		}
	}
}else

if( $step === 3 && isset($_POST['configReady']) ){

	// Validate
	switch( $config['status'] ){
		case -1:		// Doesn't Exist
			
			break;

		case 0:			// Not Readable
			
			break;

		default:		// Exists & Redable

			// Connect


			// Create Tables in MySQL

			// Done!

			break;
	}
}

// No match Return to Original Page
else{
	header("Location: ".$_SERVER['PHP_SELF']);
}
?>