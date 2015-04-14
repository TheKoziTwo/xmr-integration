<?php
define('MINIMUM_PHP_VERSION','5.3.3');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Install</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>Install Steps</h1>
            </div>
            
            <p>
            <table class="table table-condensed">
                <tr class="success"><td colspan="2">Server requirements are: </td></tr>
                <tr><th>PHP 5.3.3+</th><td><?php echo (version_compare(MINIMUM_PHP_VERSION,PHP_VERSION) !== 1 ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">Too old version of PHP, you are running '.PHP_VERSION.'<span>');?></td></tr>
                <tr><th>MySQLi</th><td><?php echo (function_exists('mysqli_connect') === true ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">mysqli extension is not enabled<span>');?></td></tr>
                <tr><th>BCMath</th><td><?php echo (function_exists('bcscale') === true ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">bcmath extension is not enabled<span>');?></td></tr>
                <tr><th>PHP Short Tags</th><td><?php echo (ini_get("short_open_tag") ? '<span class="label label-success">OK</span>' : '<span class="label label-danger">short_open_tag is not enabled<span>' )?></td></tr>
            </table>
            <?php if((function_exists('mysqli_connect') !== true) OR (function_exists('bcscale') !== true) OR ( ! ini_get("short_open_tag")) OR (version_compare(MINIMUM_PHP_VERSION,PHP_VERSION) === 1)) { ?>
            <div class="panel panel-danger">
                <div class="panel-body"><strong>WARNING!</strong> Your server does not fulfill the requirements, please look into the errors above before proceeding.</div>
            </div>
            <?php } else { ?>
            <div class="panel panel-success">
                <div class="panel-body"><strong>Congratulations!</strong> Your server fulfills all the requirements.</div>
            </div>
            <?php } ?>
        
            </p>
            
            <ol>
                <li>Open <strong>config.php</strong> and enter your database details. Please follow the steps below to install.</li>
                <pre>
'database' => array(
        'hostname' => 'localhost',
        'username' => 'your_username',
        'password' => 'your_password',
        'database' => 'your_database',
),</pre>
                <li>Import database.sql into your database</li>
                <textarea name="sql" style="width:100%;height:400px;" readonly="readonly"><?php
                    $handle = @fopen('database.sql','r');
                    if($handle)
                    {
                        $db = fread($handle, filesize('database.sql'));
                        fclose($handle);
                        echo $db;
                    }
                ?></textarea>
                <li>By default the user <strong>admin</strong> is assigned admin rights, sign up with username "admin", use a strong password</li>
                <li>You can change admins in <strong>config.php</strong> at any time, e.g if you want user "admin", "jack" and "john" to be admins it should look like this:
                <pre>
'admins' => array(
    // List all usernames here whom you'd like to assign admin access
    'admin','jack','john'
),</pre>
                </li>
                <li>Now setup the XMR daemon and wallet, if you are unsure how, you can read how to do that <a href="https://moneroeconomy.com/xmr-integration">https://moneroeconomy.com/xmr-integration</a></li>
            </ol>
        </div>
    </body>
</html>