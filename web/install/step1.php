<?php
// $Id$

/*
* MRBS Installation - Step 1 (Required)
* Check if the database exists. If not, then create it.
* Drop tables if they exist and then create them fresh.
* Add core data/settings.
*/

require_once "../systemdefaults.inc.php";
require_once "../config.inc.php";
?>

<html>
    <head>
        <title>Install MRBS - Step 1</title>
        <script type="text/javascript" src="xhconn.js"></script>
        <script type="text/javascript">
            function createDB(form) {
                var success = false;
                var fail_msg = '';
                //initialize XHConn (if XHConn isn't created successfully, the client doesn't support Ajax)
                var ajaxConn = new XHConn();
                if (!ajaxConn) alert("XMLHTTP not available. Have you disabled javascript?");

                var fnDone = function (oXML) {
                    d = document.createElement('div');
                    d.innerHTML = oXML.responseText;
                    document.getElementById('content').innerHTML = oXML.responseText;
                    eval(oXML.responseText); //execute the returned JS code
                    if (success) {location.href = 'step2.php';} else {alert(fail_msg);}
                }
                var postvars = "un="+document.getElementById('user').value;
                postvars += "&pw="+document.getElementById('pw').value;
                // (sURL, sMethod, sVars, fnDone)
                ajaxConn.connect("create_db.php", "POST", postvars, fnDone);
            }

        </script>
        <style type="text/css">
            dl {
                clear:both;
            }
            dt {
                float: left;
                width: 215px;
                font-weight: bold;
            }
            dd {
                margin: 0 0 0 215px;
            }

        </style>
    </head>
    <body>
        <h1>Step 1 - Install MRBS Core</h1>

        <p>Please note: This currently only works with MySQL. If MRBS is already installed in the database shown below, it will be <b>overwritten.</b></p>
        <p>The administrator user below will be used to create the MRBS database and new user that will be used by MRBS to access the database.</p>

        <form id="step1" action="#">
            <fieldset>
                <legend>Administrator Login Details</legend>
                <dl>
                    <dt>Username</dt>
                    <dd><input id="user" type="text" value="root" /></dd>
                    <dt>Password</dt>
                    <dd><input id="pw" type="text" value="" /></dd>
                </dl>
            </fieldset>
            <fieldset>
                <legend>Database Server Details</legend>
                <dl>
                    <dt>Database type</dt>
                    <dd><?php echo $dbsys ?></dd>
                </dl>
                <dl>
                    <dt>Hostname</dt>
                    <dd><?php echo $db_host ?></dd>
                </dl>
                <dl>
                    <dt>Database name</dt>
                    <dd><?php echo $db_database ?></dd>
                </dl>
                <dl>
                    <dt>MRBS username</dt>
                    <dd><?php echo $db_login ?></dd>
                </dl>
                <dl>
                    <dt>MRBS password</dt>
                    <dd><?php echo $db_password ?></dd>
                </dl>
                <dl>
                    <dt>Tablename prefix</dt>
                    <dd><?php echo $db_tbl_prefix ?></dd>                
                </dl>
                <p>To change the details above please change them in config.inc.php and then reload this page.</p>
            </fieldset>
        </form>
        <p><a href="#" onClick="createDB(this.form); return false">Next</a></p>
        <div id="content"></div>
    </body>
</html>
