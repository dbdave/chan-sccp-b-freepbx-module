<?php 
// This file is part of FreePBX.
//
//    FreePBX is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    FreePBX is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//    Copyright (C) 2006 Niklas Larsson
//    Copyright (C) 2006 Rob Thomas <xrobau@gmail.com>
//

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$dispnum = "featurecodeadmin"; //used for switch on config.php
$devices = sccp_devices_list();
$lines = sccp_lines_list();
$tabindex = 0;

//if submitting form, update database
?>
<style>
	form.sccp_form input[type=text] { width:100%;  }
	form.sccp_form select { width:100%;  }
</style>
<div class="rnav">
<ul>
<li><a class="current" href="config.php?type=setup&display=sccpline&action=insert">Add Line</a></li>
<?php
foreach ($lines as $line ){
	?>
			<li><a href="?menu=pbxconfig&type=setup&display=sccpline&action=update&line=<?=$line["name"]?>"><?=$line["name"]?></a> <?=$line["description"]?></li>
		<?php 
		}
		?>
</ul>
</div>

<?php 
if ($action==""){$action="insert";};
switch ($action){
	case "":
		

	break;
	case "do_restart":
		$response = ($astman->send_request('Command',array('Command'=>"sccp restart " . $_GET["device"] )));
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_GET["device"] );
	break;
	case "do_linedelete":
		sccp_delete_line($_GET["line"]);
		header("location:?menu=pbxconfig&type=setup&display=sccpline"); 
		break;
	case "do_reset":
		$response = ($astman->send_request('Command',array('Command'=>"sccp reset " . $_GET["device"] )));
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_GET["device"] );
	break;
	case "do_update":
		
		$vars = $_POST;
		$vars["id"]=$vars["name"];
		sccp_update_line($vars);
		
		header("location:?menu=pbxconfig&type=setup&display=sccpline&action=update&line=" .$_POST["name"] );
		
	break;
	case "update":
		$results = sql("SELECT * FROM sccpline where name='" . $_GET["line"] .  "' ","getAll",DB_FETCHMODE_ASSOC);
		if(is_array($results)){
?>
		<form method="post" action="?menu=pbxconfig&type=setup&display=sccpline&action=do_linedelete&line=<?=@$results[0]["name"]?>" onsubmit="return confirm('Are you sure?');">
			<button type="submit">Delete Line</button>
		</form>
		<form class="sccp_form" method="post" action="?menu=pbxconfig&type=setup&display=sccpline&action=do_update">
			<table>
				<tr>
					<td>name</td>
					<td><input readonly type="text" id="name" name="name" value="<?=$results[0]["name"]?>"/></td>
					<td>line name (number)</td>
				</tr>
				<tr>
					<td>pin</td>
					<td><input type="text" id="pin" name="pin" value="<?=$results[0]["pin"]?>"/></td>
					<td>pin</td>
				</tr>
				<tr>
					<td>label</td>
					<td><input type="text" id="label" name="label" value="<?=$results[0]["label"]?>"/></td>
					<td>label</td>
				</tr>
				<tr>
					<td>description</td>
					<td><input type="text" id="description" name="description" value="<?=$results[0]["description"]?>"/></td>
					<td>description</td>
				</tr>
				<tr>
					<td>context</td>
					<td><input type="text" id="context" name="context" value="<?=$results[0]["context"]?>"/></td>
					<td>pbx dialing context</td>
				</tr>
				<tr>
					<td>incominglimit</td>
					<td><input type="text" id="incominglimit" name="incominglimit" value="<?=$results[0]["incominglimit"]?>"/></td>
					<td>allow x number of incoming calls (call waiting)</td>
				</tr>
				<tr>
					<td>transfer</td>
					<td><input type="text" id="transfer" name="transfer" value="<?=$results[0]["transfer"]?>"/></td>
					<td>per line transfer capability</td>
				</tr>
				<tr>
					<td>mailbox</td>
					<td><input type="text" id="mailbox" name="mailbox" value="<?=$results[0]["mailbox"]?>"/></td>
					<td>Mailbox to store messages in</td>
				</tr>
				<tr>
					<td>vmnum</td>
					<td><input type="text" id="vmnum" name="vmnum" value="<?=$results[0]["vmnum"]?>"/></td>
					<td>Number to dial to get to the users Mailbox</td>
				</tr>
				<tr>
					<td>cid_name</td>
					<td><input type="text" id="cid_name" name="cid_name" value="<?=$results[0]["cid_name"]?>"/></td>
					<td>(REQUIRED) callerid name</td>
				</tr>
				<tr>
					<td>cid_num</td>
					<td><input type="text" id="cid_num" name="cid_num" value="<?=$results[0]["cid_num"]?>"/></td>
					<td>(REQUIRED) callerid number</td>
				</tr>
				<tr>
					<td>trnsfvm</td>
					<td><input type="text" id="trnsfvm" name="trnsfvm" value="<?=$results[0]["trnsfvm"]?>"/></td>
					<td>extension to redirect the caller to for voice mail</td>
				</tr>
				<tr>
					<td>secondary_dialtone_digits</td>
					<td><input type="text" id="secondary_dialtone_digits" name="secondary_dialtone_digits" value="<?=$results[0]["secondary_dialtone_digits"]?>"/></td>
					<td>Digits to indicate an external line to user (secondary dialtone) (max 9 digits)</td>
				</tr>
				<tr>
					<td>secondary_dialtone_tone</td>
					<td><input type="text" id="secondary_dialtone_tone" name="secondary_dialtone_tone" value="<?=$results[0]["secondary_dialtone_tone"]?>"/></td>
					<td>outside dialtone frequency</td>
				</tr>
				<tr>
					<td>musicclass</td>
					<td><input type="text" id="musicclass" name="musicclass" value="<?=$results[0]["musicclass"]?>"/></td>
					<td>Sets the default music on hold class</td>
				</tr>
				<tr>
					<td>language</td>
					<td><input type="text" id="lang" name="language" value="<?=$results[0]["language"]?>"/></td>
					<td>Default language setting</td>
				</tr>
				<tr>
					<td>accountcode</td>
					<td><input type="text" id="accountcode" name="accountcode" value="<?=$results[0]["accountcode"]?>"/></td>
					<td>Accountcode to ease billing</td>
				</tr>
				<tr>
					<td>echocancel</td>
					<td><input type="text" id="echocancel" name="echocancel" value="<?=$results[0]["echocancel"]?>"/></td>
					<td>sets the phone echocancel for this line</td>
				</tr>
				<tr>
					<td>silencesuppression</td>
					<td><input type="text" id="silencesuppression" name="silencesuppression" value="<?=$results[0]["silencesuppression"]?>"/></td>
					<td>sets the silence suppression for this line</td>
				</tr>
				<tr>
					<td>callgroup</td>
					<td><input type="text" id="callgroup" name="callgroup" value="<?=$results[0]["callgroup"]?>"/></td>
					<td>sets the caller groups this line is a member of</td>
				</tr>
				<tr>
					<td>pickupgroup</td>
					<td><input type="text" id="pickupgroup" name="pickupgroup" value="<?=$results[0]["pickupgroup"]?>"/></td>
					<td>sets the pickup groups this line is a member of (this phone can pickup calls from remote phones which are in this caller group</td>
				</tr>
				<tr>
					<td>amaflags</td>
					<td><input type="text" id="amaflags" name="amaflags" value="<?=$results[0]["amaflags"]?>"/></td>
					<td>sets the AMA flags stored in the CDR record for this line</td>
				</tr>
				<tr>
					<td>dnd</td>
					<td>
						<select name="dnd" id="dnd">
							<option value="off" <? if ($results[0]["dnd"]=="off"){echo "selected";};?>>off</option>
							<option value="on" <? if ($results[0]["dnd"]=="on"){echo "selected";};?>>on</option>
							<option value="reject" <? if ($results[0]["dnd"]=="reject"){echo "selected";};?>>reject</option>
							<option value="silent" <? if ($results[0]["dnd"]=="silent"){echo "selected";};?>>silent</option>
						</select>
					</td>
					<td> allow setting dnd for this line. Valid values are 'off', 'on' (busy signal), 'reject' (busy signal), 'silent' (ringer = silent) or user to toggle on pho
ne</td>
				</tr>
				<tr>
					<td>setvar</td>
					<td><input type="text" id="setvar" name="setvar" value="<?=$results[0]["setvar"]?>"/></td>
					<td>
					(MULTI-ENTRY) extra variables to be set on line initialization multiple entries possible (for example the sip number to use when dialing outside) <br/>
					(MULTI-ENTRY) format setvar=param=value, for example setvar=sipno=12345678</td>
				</tr>				
				<tr>
				
					<td><button type="submit">Update</button></td>
				</tr>
			</table>
			
		</form>
		
		<?php 
		}
	break;
	case "do_insert":
		$vars = $_POST;
		$vars["id"]=$vars["name"];
		sccp_add_line($vars);
		header("location:?menu=pbxconfig&type=setup&display=sccpline&action=update&device=" .$_POST["name"] );
		
	break;
	case "insert":
		?>
		<form class="sccp_form" method="post" action="?menu=pbxconfig&type=setup&display=sccpline&action=do_insert">
			
			<table>
				<tr>
					<td>name</td>
					<td><input type="text" id="name" name="name" value=""/></td>
					<td>line name (number)</td>
				</tr>
				<tr>
					<td>pin</td>
					<td><input type="text" id="pin" name="pin" value="1234"/></td>
					<td>pin</td>
				</tr>
				<tr>
					<td>label</td>
					<td><input type="text" id="label" name="label" value=""/></td>
					<td>label</td>
				</tr>
				<tr>
					<td>description</td>
					<td><input type="text" id="description" name="description" value=""/></td>
					<td>description</td>
				</tr>
				<tr>
					<td>context</td>
					<td><input type="text" id="context" name="context" value="from-internal"/></td>
					<td>pbx dialing context</td>
				</tr>
				<tr>
					<td>incominglimit</td>
					<td><input type="text" id="incominglimit" name="incominglimit" value="5"/></td>
					<td>allow x number of incoming calls (call waiting)</td>
				</tr>
				<tr>
					<td>transfer</td>
					<td><input type="text" id="transfer" name="transfer" value="on"/></td>
					<td>per line transfer capability</td>
				</tr>
				<tr>
					<td>mailbox</td>
					<td><input type="text" id="mailbox" name="mailbox" value=""/></td>
					<td>Mailbox to store messages in</td>
				</tr>
				<tr>
					<td>vmnum</td>
					<td><input type="text" id="vmnum" name="vmnum" value=""/></td>
					<td>Number to dial to get to the users Mailbox</td>
				</tr>
				<tr>
					<td>cid_name</td>
					<td><input type="text" id="cid_name" name="cid_name" value=""/></td>
					<td>(REQUIRED) callerid name</td>
				</tr>
				<tr>
					<td>cid_num</td>
					<td><input type="text" id="cid_num" name="cid_num" value=""/></td>
					<td>(REQUIRED) callerid number</td>
				</tr>
				<tr>
					<td>trnsfvm</td>
					<td><input type="text" id="trnsfvm" name="trnsfvm" value=""/></td>
					<td>extension to redirect the caller to for voice mail</td>
				</tr>
				<tr>
					<td>secondary_dialtone_digits</td>
					<td><input type="text" id="secondary_dialtone_digits" name="secondary_dialtone_digits" value=""/></td>
					<td>Digits to indicate an external line to user (secondary dialtone) (max 9 digits)</td>
				</tr>
				<tr>
					<td>secondary_dialtone_tone</td>
					<td><input type="text" id="secondary_dialtone_tone" name="secondary_dialtone_tone" value="0x22"/></td>
					<td>outside dialtone frequency</td>
				</tr>
				<tr>
					<td>musicclass</td>
					<td><input type="text" id="musicclass" name="musicclass" value="default"/></td>
					<td>Sets the default music on hold class</td>
				</tr>
				<tr>
					<td>language</td>
					<td><input type="text" id="lang" name="language" value="en"/></td>
					<td>Default language setting</td>
				</tr>
				<tr>
					<td>accountcode</td>
					<td><input type="text" id="accountcode" name="accountcode" value=""/></td>
					<td>Accountcode to ease billing</td>
				</tr>
				<tr>
					<td>echocancel</td>
					<td><input type="text" id="echocancel" name="echocancel" value="on"/></td>
					<td>sets the phone echocancel for this line</td>
				</tr>
				<tr>
					<td>silencesuppression</td>
					<td><input type="text" id="silencesuppression" name="silencesuppression" value="off"/></td>
					<td>sets the silence suppression for this line</td>
				</tr>
				<tr>
					<td>callgroup</td>
					<td><input type="text" id="callgroup" name="callgroup" value=""/></td>
					<td>sets the caller groups this line is a member of</td>
				</tr>
				<tr>
					<td>pickupgroup</td>
					<td><input type="text" id="pickupgroup" name="pickupgroup" value=""/></td>
					<td>sets the pickup groups this line is a member of (this phone can pickup calls from remote phones which are in this caller group</td>
				</tr>
				<tr>
					<td>amaflags</td>
					<td><input type="text" id="amaflags" name="amaflags" value=""/></td>
					<td>sets the AMA flags stored in the CDR record for this line</td>
				</tr>
				<tr>
					<td>dnd</td>
					<td>
						<select name="dnd" id="dnd">
							<option value="off" selected>off</option>
							<option value="on">on</option>
							<option value="reject">reject</option>
							<option value="silent">silent</option>
						</select>
					</td>
					<td> allow setting dnd for this line. Valid values are 'off', 'on' (busy signal), 'reject' (busy signal), 'silent' (ringer = silent) or user to toggle on pho
ne</td>
				</tr>
				<tr>
					<td>setvar</td>
					<td><input type="text" id="setvar" name="setvar" value=""/></td>
					<td>
					(MULTI-ENTRY) extra variables to be set on line initialization multiple entries possible (for example the sip number to use when dialing outside) <br/>
					(MULTI-ENTRY) format setvar=param=value, for example setvar=sipno=12345678</td>
				</tr>				
				<tr>
				
					<td><button type="submit">Insert</button></td>
				</tr>
			</table>
			
		</form>
		
		<?php 
	break;
}



?>