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
include dirname(__FILE__)."/class.astinfo.php";

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$dispnum = "featurecodeadmin"; //used for switch on config.php
$devices = sccp_devices_list();
$lines = sccp_lines_list();
$tabindex = 0;  
check_schema_device ();

//$astinfo = new astinfo($astman);
$devlist = array("7910","7911","7912","7921","7922","7940","7941","7942","7945","7960","7961","7962","7965","7970","7971","7972","7975");
$devdefault = "7965";
?>
<style>
	form.sccp_form input[type=text] { width:100%;  }
	form.sccp_form select { width:100%;  }
	.rnav ul { max-height: none; } 
</style>
<div class="rnav">
<ul><?php
$response = ($astman->send_request('Command',array('Command'=>"sccp show devices")));
//print_r($response);
$astout = explode("\n",$response["data"]);
foreach ($astout as $line) {
	
	//	print_r($line);
	if (preg_match('/\|\s{1,}([A-Za-z ]+)\s{2,}(--|[0-9]+.[0-9]+.[0-9]+.[0-9]+)\s{2,}(SEP[0-9A-F]+)\s{1,}(Failed|OK)\s+([A-Za-z]+)\s+([A-Za-z]+)\s+([A-Za-z]+)\s+([0-9]+)\s([0-9:]+)\s([0-9]+)\s+([A-Za-z]+)\s+([0-9]+)/', $line, $matches)) {
		
		/*echo $line;*/
		//print_r($matches);
		if (trim($matches[1])==""){
			echo "<li>";
			//print_r($matches);
			echo "No Configuration";
			echo "<a href='?menu=pbxconfig&type=setup&display=sccpdevice&action=insert&device=" . $matches[3]  . "'>";
			echo $matches[3];
			echo "</a>";
			echo " ";
			echo $matches[2];
			echo " ";
			echo "</li>";
			
		}
		
	}
	
} 
?>
</ul>
<ul>
<li><a class="current" href="config.php?type=setup&display=sccpdevice&action=insert">Add Device</a></li>
<?php 
		foreach ($devices as $device ){
		?> 
			<li><a href="?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=<?=$device["name"]?>"><?=$device["name"]?></a> <?=$device["description"]?></li>
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
	case "do_addbutt": 
		sccp_add_button($_POST);
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_GET["device"] );
	break;
	case "do_buttdelete":
		$results = sql("delete from buttonconfig where device='".$_GET["device"]."' and instance='".$_GET["instance"]."' ","getAll",DB_FETCHMODE_ASSOC);
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_GET["device"] );
	break;
	case "do_restart":
		$response = ($astman->send_request('Command',array('Command'=>"sccp restart " . $_GET["device"] )));
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_GET["device"] );
	break;
	case "do_reset":
		$response = ($astman->send_request('Command',array('Command'=>"sccp reset " . $_GET["device"] )));
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_GET["device"] );
	break;
	case "do_update":
		sccp_update_device($_POST);
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=".$_POST["name"]); 
	break;
	case "do_devdelete":
		sccp_delete_device($_GET["device"]);
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice"); 
		break;
	case "do_devmsg":
		$response = ($astman->send_request('Command',array('Command'=>"sccp message device " . $_GET["device"]  . " \"" . $_POST["msg"] . "\"" ) ));
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=". $_GET["device"]);
	break;
	case "update":
		$results = sql("SELECT * FROM sccpdevice where name='" . $_GET["device"] .  "' ","getAll",DB_FETCHMODE_ASSOC);
		if(is_array($results)){

		
		?>
		<table>
			<tr>
				<td>
		<form method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_restart&device=<?=@$results[0]["name"]?>">
			<button type="submit">restart</button>
		</form>
				</td>
				<td>
		<form method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_reset&device=<?=@$results[0]["name"]?>">
			<button type="submit">reset</button>
		</form>
				</td>
				<td>
		<form method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_devmsg&device=<?=@$results[0]["name"]?>">
			<input type="text" name="msg"/>
			<button type="submit">message</button>
		</form>
				</td>
				<td>
					<form method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_devdelete&device=<?=@$results[0]["name"]?>" onsubmit="return confirm('Are you sure?');">
						<button type="submit">Delete Device</button>
					</form>
				</td>
			</tr>
		</table>
		<hr/>
		<!-- <form method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_addbutt&device=<?=@$results[0]["name"]?>"> -->
		<table>
			<tr>
				<th colspan=4>Button Config</th>
			</tr>
			<tr>
				<th>instance</th>
				<th>type</th>
				<th>name</th>
				<th>options</th>
			</tr>
		<? 
			$buttresults = sql("SELECT * FROM buttonconfig where device='" . $_GET["device"] .  "' order by instance asc","getAll",DB_FETCHMODE_ASSOC);
				if(is_array($buttresults)){
					foreach ($buttresults as $buttresult){
?>
						<tr>
							<td><?=$buttresult["instance"]?></td>
							<td><?=$buttresult["type"]?></td>
							<td><?=$buttresult["name"]?></td>
							<td><?=$buttresult["options"]?></td>
							<!-- <td><a href="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_buttdelete&device=<?=@$buttresult["device"]?>&instance=<?=@$buttresult["instance"]?>">delete</a></td>-->
						</tr>
<?php 
					}
				}
				
		?>
<!-- 			<tr>
				<td>
					<input type="hidden" name="device" value="<?=$_GET["device"]?>"/>
					<input type="text" name="instance" />
				</td>
				<td>
					<input type="text" name="type" />
				</td>
				<td>
					<input type="text" name="name" />
				</td>
				<td>
					<input type="text" name="options" />
				</td>
				<td>
					<button type="submit">Add</button>
				</td>
			</tr> -->
			<tr>
				<td colspan="4">
					<form action="?menu=pbxconfig&type=setup&display=sccpbutton&action=buttonconfig&device=<?=@$_GET["device"]?>" method="post">
						<button >Modify Button Items</button>						
					</form>
				</td>
			</tr>
		</table>
		<!-- </form> -->
		<hr/>
		<form method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_update&device=<?=@$results[0]["name"]?>">
		<table>
		<tr>
		<td>name</td>
		<td><input readonly type="text" id="name" name="name" value="<?=@$results[0]["name"]?>"/></td>
		<td>device name</td>
		</tr>
		<tr>
		<td>description</td>
		<td><input type="text" id="description" name="description" value="<?=@$results[0]["description"]?>"/></td>
		<td>device description</td>
		</tr>
		<tr>
		<td width="20%">type</td>
		<td width="20%">
		<select name="type" id="type">
							<?php foreach ($devlist as $device){ ?>
								<option <? if ($device==@$results[0]["type"]){echo "selected";} ?> value="<?=$device?>"><?=$device?></option>
							<?php }?>
							</select></td>
							<td width="60%">(REQUIRED) device type</td>
						</tr>
						<tr>
							<td>add-on</td>
							<td>
								<select name="addon" id="addon">
									<option value="" <? if (@$results[0]["addon"]==""){echo "selected"; }?>>None</option>
									<option value="7914" <? if (@$results[0]["addon"]=="7914"){echo "selected"; }?>>7914</option>
									<option value="7915" <? if (@$results[0]["addon"]=="7915"){echo "selected"; }?>>7915</option>
									<option value="7916" <? if (@$results[0]["addon"]=="7916"){echo "selected"; }?>>7916</option>
									
								</select>
							</td>
							<td>One of 7914, 7915, 7916</td>
						</tr>
						<tr>
							<td>transfer</td>
							<td>
								<select name="transfer" id="transfer">
									<option value="on" <? if (@$results[0]["transfer"]=="on"){echo "selected"; }?>>on</option>					
									<option value="off" <? if (@$results[0]["transfer"]=="off"){echo "selected"; }?>>off</option>
								</select>
							</td>
							<td>
								enable or disable the transfer capability. It does remove the transfer softkey
							</td>
						</tr>
						<tr>
							<td>dtmfmode</td>
							<td>
								<select name="dtmfmode" id="dtmfmode">
									<option value="outofband" <? if (@$results[0]["dtmfmode"]=="outofband"){echo "selected"; }?>>outofband</option>					
									<option value="inband" <? if (@$results[0]["dtmfmode"]=="inband"){echo "selected"; }?>>inband</option>
								</select>
							</td>
							<td>
								inband or outofband. outofband is the native cisco dtmf tone play.<br/>
								Some phone model does not play dtmf tones while connected (bug?), so the default is inband
							</td>
						</tr>
						<tr>
							<td>imageversion</td>
							
							<td>
								<input type="text" name="imageversion" value="<?=@$results[0]["imageversion"]?>"/>
							</td>
							<td>useful to upgrade old firmwares (the ones that do not load *.xml from the tftp server)</td>
						</tr>
						<tr>
							<td>deny</td>
							<td><input type="text" name="deny" value="<?=@$results[0]["deny"]?>"/></td>
							<td>(MULTI-ENTRY) Same as general</td>
						</tr>
						<tr>
							<td>permit</td>
							<td><input type="text" name="permit" value="<?=@$results[0]["permit"]?>"/></td>
							<td>(MULTI-ENTRY) This device can register only using this ip address</td>
						</tr>
						<tr>
							<td>dndFeature</td>
							<td>
								<select name="dndFeature" id="dndFeature">
									<option value="yes" <? if (@$results[0]["dndFeature"]=="yes"){echo "selected"; }?>>yes</option>					
									<option value="no" <? if (@$results[0]["dndFeature"]=="no"){echo "selected"; }?>>no</option>
								</select>
							</td>
							<td>allow usage do not disturb button</td>
						</tr>
						<tr>
							<td>trustphoneip</td>
							<td>
								<select name="trustphoneip" id="trustphoneip">
									<option value="yes" <? if (@$results[0]["trustphoneip"]=="yes"){echo "selected"; }?>>yes</option>					
									<option value="no" <? if (@$results[0]["trustphoneip"]=="no"){echo "selected"; }?>>no</option>
								</select>
							</td>
							<td>The phone has a ip address. It could be private, so if the phone is behind NAT</td>
						</tr>
						<tr>
							<td>directrtp</td>
							<td>
								<select name="directrtp" id="directrtp">
									<option value="off" <? if (@$results[0]["directrtp"]=="off"){echo "selected"; }?>>off</option>					
									<option value="on" <? if (@$results[0]["directrtp"]=="on"){echo "selected"; }?>>on</option>
								</select>
							</td>
							<td>This option allow devices to do direct RTP sessions (default Off)</td>
						</tr>
						<tr>
							<td>earlyrtp</td>
							<td>
								<select name="earlyrtp" id="earlyrtp">
									<option value="none" <? if (@$results[0]["earlyrtp"]=="none"){echo "selected"; }?>>none</option>
									<option value="offhook" <? if (@$results[0]["earlyrtp"]=="offhook"){echo "selected"; }?>>offhook</option>
									<option value="dial" <? if (@$results[0]["earlyrtp"]=="dial"){echo "selected"; }?>>dial</option>					
									<option value="ringout" <? if (@$results[0]["earlyrtp"]=="ringout"){echo "selected"; }?>>ringout</option>
									<option value="progress" <? if (@$results[0]["earlyrtp"]=="progress"){echo "selected"; }?>>progress</option>
								</select>
							</td>
							<td>valid options: none, offhook, dial, ringout and progress. default is progress. The audio stream will be open in the progress and connected state by default.</td>
						</tr>
						<tr>
							<td>mwilamp</td>
							<td>
								<select name="mwilamp" id="mwilamp">
									<option value="on" <? if (@$results[0]["mwilamp"]=="on"){echo "selected"; }?>>on</option>					
									<option value="off" <? if (@$results[0]["mwilamp"]=="off"){echo "selected"; }?>>off</option>
									<option value="wink" <? if (@$results[0]["mwilamp"]=="wink"){echo "selected"; }?>>wink</option>
									<option value="flash" <? if (@$results[0]["mwilamp"]=="flash"){echo "selected"; }?>>flash</option>
									<option value="blink" <? if (@$results[0]["mwilamp"]=="blink"){echo "selected"; }?>>blink</option>
								</select>
							</td>
							<td> Set the MWI lamp style when MWI active to on, off, wink, flash or blink</td>
						</tr>			
						<tr>
							<td>mwioncall</td>
							<td><input type="text" name="mwioncall" value="<?=@$results[0]["mwioncall"]?>"></td>
							<td> Set the MWI on call.</td>
						</tr>
						<tr>
							<td>pickupexten</td>
							<td>
								<select name="pickupexten" id="pickupexten">
									<option value="yes" <? if (@$results[0]["pickupexten"]=="yes"){echo "selected"; }?>>yes</option>					
									<option value="no" <? if (@$results[0]["pickupexten"]=="no"){echo "selected"; }?>>no</option>
								</select>
							</td>
							<td>enable Pickup function to direct pickup an extension</td>
						</tr>
						<tr>
							<td>pickupcontext</td>
							<td>
								<input type="text" name="pickupcontext" id="pickupcontext" value="<?=@$results[0]["pickupcontext"]?>" />
							</td>
							<td>context where direct pickup search for extensions. if not set it will be ignored.</td>
						</tr>
						<tr>
							<td>pickupmodeanswer</td>
							<td>
								<input type="text" name="pickupmodeanswer" id="pickupmodeanswer" value="<?=@$results[0]["pickupmodeanswer"]?>" />
							</td>
							<td> on = asterisk way, the call has been answered when picked up</td>
						</tr>
						<tr>
							<td>private</td>
							<td><select name="private" id="private">
									<option value="on" <? if (@$results[0]["private"]=="on"){echo "selected"; }?>>on</option>					
									<option value="off" <? if (@$results[0]["private"]=="off"){echo "selected"; }?>>off</option>
								</select></td>
							<td>permit the private function softkey</td>
						</tr>
						<tr>
							<td>privacy</td>
							<td><input type="text" name="privacy" id="privacy" value="<?=@$results[0]["privacy"]?>" /></td>
							<td> permit the private function softkey for this device</td>
						</tr>
						<tr>
							<td>nat</td>
							<td>
								<select name="nat" id="nat">
									<option value="off" <? if (@$results[0]["nat"]=="off"){echo "selected"; }?>>off</option>					
									<option value="on" <? if (@$results[0]["nat"]=="on"){echo "selected"; }?>>on</option>
								</select>
							</td>
							<td>Device NAT support (default Off)</td>
						</tr>
						<tr> 
							<td>softkeyset</td>
							<td><input type="text" id="softkeyset" name="softkeyset" value="<?=@$results[0]["softkeyset"]?>"/></td>
							<td>use specified softkeyset with name softkeyset1</td>
						</tr>
						<tr>
							<td>audio_tos</td>
							<td><input type="text" id="audio_tos" name="audio_tos" value="<?=@$results[0]["audio_tos"]?>"/></td>
							<td>sets the default audio/rtp packets Type of Service (TOS)       (defaults to 0xb8 = 10111000 = 184 = DSCP:101110 = EF)</td>
						</tr>
						<tr>
							<td>audio_cos</td>
							<td><input type="text" id="audio_cos" name="audio_cos" value="<?=@$results[0]["audio_cos"]?>"/></td>
							<td>sets the default audio/rtp packets Class of Service (COS)      (defaults to 6)</td>
						</tr>
						<tr>
							<td>video_tos</td>
							<td><input type="text" id="video_tos" name="video_tos" value="<?=@$results[0]["video_tos"]?>"/></td>
							<td>sets the default video/rtp packets Type of Service (TOS)       (defaults to 0x88 = 10001000 = 136 = DSCP:100010 = AF41)</td>
						</tr>
						<tr>
							<td>video_cos</td>
							<td><input type="text" id="video_cos" name="video_cos" value="<?=@$results[0]["video_cos"]?>"/></td>
							<td>sets the default video/rtp packets Class of Service (COS)      (defaults to 5)</td>
						</tr>
						<tr>
							<td>setvar</td>
							<td><input type="text" id="setvar" name="setvar" value="<?=@$results[0]["setvar"]?>"/></td>
							<td> (MULTI-ENTRY) extra variables to be set on line initialization multiple entries possible (for example the sip number to use when dialing outside)</td>
						</tr>
						<tr>
							<td>disallow</td>
							<td><input type="text" id="disallow" name="disallow" value="<?=@$results[0]["disallow"]?>"/></td>
							<td>(REQUIRED) (MULTI-ENTRY) First disallow all codecs, for example 'all'</td>
						</tr>
						<tr>
							<td>allow</td>
							<td><input type="text" id="allow" name="allow" value="<?=@$results[0]["allow"]?>"/></td>
							<td>(REQUIRED) (MULTI-ENTRY) Allow codecs in order of preference (Multiple lines allowed)</td>
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
		sccp_add_device($_POST);
		header("location:?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=" .$_POST["name"] );
	break;
	
	case "insert":
		?>
		<form class="sccp_form" method="post" action="?menu=pbxconfig&type=setup&display=sccpdevice&action=do_insert">
			
			<table>
				<tr>
					<td>name</td>
					<td><input type="text" id="name" name="name" value="<?=@$_GET["device"]?>"/></td>
					<td>device name</td>
				</tr>
				<tr>
					<td>description</td>
					<td><input type="text" id="description" name="description" value="<?=@$_GET["device"]?>"/></td>
					<td>device description</td>
				</tr>
				<tr>
					<td width="20%">type</td>
					<td width="20%">
					<select name="type" id="type">
					<?php foreach ($devlist as $device){ ?>
						<option <? if ($device==$devdefault){echo "selected";} ?> value="<?=$device?>"><?=$device?></option>
					<?php }?>
					</select></td>
					<td width="60%">(REQUIRED) device type</td>
				</tr>
				<tr>
					<td>add-on</td>
					<td>
						<select name="addon" id="addon">
							<option value="" selected>None</option>
							<option value="7914">7914</option>
							<option value="7915">7915</option>
							<option value="7916">7916</option>
							
						</select>
					</td>
					<td>One of 7914, 7915, 7916</td>
				</tr>
				<tr>
					<td>transfer</td>
					<td>
						<select name="transfer" id="transfer">
							<option value="on" selected>on</option>					
							<option value="on">off</option>
						</select>
					</td>
					<td>
						enable or disable the transfer capability. It does remove the transfer softkey
					</td>
				</tr>
				<tr>
					<td>dtmfmode</td>
					<td>
						<select name="dtmfmode" id="dtmfmode">
							<option value="outofband" selected>outofband</option>					
							<option value="inband">inband</option>
						</select>
					</td>
					<td>
						inband or outofband. outofband is the native cisco dtmf tone play.<br/>
						Some phone model does not play dtmf tones while connected (bug?), so the default is inband
					</td>
				</tr>
				<tr>
					<td>imageversion</td>
					
					<td>
						<input type="text" name="imageversion"/>
					</td>
					<td>useful to upgrade old firmwares (the ones that do not load *.xml from the tftp server)</td>
				</tr>
				<tr>
					<td>deny</td>
					<td><input type="text" name="deny"/></td>
					<td>(MULTI-ENTRY) Same as general</td>
				</tr>
				<tr>
					<td>permit</td>
					<td><input type="text" name="permit"/></td>
					<td>(MULTI-ENTRY) This device can register only using this ip address</td>
				</tr>
				<tr>
					<td>dndFeature</td>
					<td>
						<select name="dndFeature" id="dndFeature">
							<option value="yes" selected>yes</option>					
							<option value="no">no</option>
						</select>
					</td>
					<td>allow usage do not disturb button</td>
				</tr>
				<tr>
					<td>trustphoneip</td>
					<td>
						<select name="trustphoneip" id="trustphoneip">
							<option value="yes" selected>yes</option>					
							<option value="no">no</option>
						</select>
					</td>
					<td>The phone has a ip address. It could be private, so if the phone is behind NAT</td>
				</tr>
				<tr>
					<td>directrtp</td>
					<td>
						<select name="directrtp" id="directrtp">
							<option value="off" >off</option>					
							<option value="on" selected>on</option>
						</select>
					</td>
					<td>This option allow devices to do direct RTP sessions (default Off)</td>
				</tr>
				<tr>
					<td>earlyrtp</td>
					<td>
						<select name="earlyrtp" id="earlyrtp">
							<option value="none" >none</option>
							<option value="offhook" >offhook</option>
							<option value="dial" >dial</option>					
							<option value="ringout" >ringout</option>
							<option value="progress" selected>progress</option>
						</select>
					</td>
					<td>valid options: none, offhook, dial, ringout and progress. default is progress. The audio stream will be open in the progress and connected state by default.</td>
				</tr>
				<tr>
					<td>mwilamp</td>
					<td>
						<select name="mwilamp" id="mwilamp">
							<option value="on" selected>on</option>					
							<option value="off">off</option>
							<option value="wink">wink</option>
							<option value="flash">flash</option>
							<option value="blink">blink</option>
						</select>
					</td>
					<td> Set the MWI lamp style when MWI active to on, off, wink, flash or blink</td>
				</tr>			
				<tr>
					<td>wmioncall</td>
					<td></td>
					<td> Set the MWI on call.</td>
				</tr>
				<tr>
					<td>pickupexten</td>
					<td>
						<select name="pickupexten" id="pickupexten">
							<option value="yes" selected>yes</option>					
							<option value="no">no</option>
						</select>
					</td>
					<td>enable Pickup function to direct pickup an extension</td>
				</tr>
				<tr>
					<td>pickupcontext</td>
					<td>
						<input type="text" name="pickupcontext" id="pickupcontext" />
					</td>
					<td>context where direct pickup search for extensions. if not set it will be ignored.</td>
				</tr>
				<tr>
					<td>pickupmodeanswer</td>
					<td>
						<input type="text" name="pickupmodeanswer" id="pickupmodeanswer" value="on" />
					</td>
					<td> on = asterisk way, the call has been answered when picked up</td>
				</tr>
				<tr>
					<td>private</td>
					<td><select name="private" id="private">
							<option value="on" selected>on</option>					
							<option value="off">no</option>
						</select></td>
					<td>permit the private function softkey</td>
				</tr>
				<tr>
					<td>privacy</td>
					<td><input type="text" name="privacy" id="privacy" value="full" /></td>
					<td> permit the private function softkey for this device</td>
				</tr>
				<tr>
					<td>nat</td>
					<td>
						<select name="nat" id="nat">
							<option value="off" selected>off</option>					
							<option value="on">on</option>
						</select>
					</td>
					<td>Device NAT support (default Off)</td>
				</tr>
				<tr> 
					<td>softkeyset</td>
					<td><input type="text" id="softkeyset" name="softkeyset" value=""/></td>
					<td>use specified softkeyset with name softkeyset1</td>
				</tr>
				<tr>
					<td>audio_tos</td>
					<td><input type="text" id="audio_tos" name="audio_tos" value="0xb8"/></td>
					<td>sets the default audio/rtp packets Type of Service (TOS)       (defaults to 0xb8 = 10111000 = 184 = DSCP:101110 = EF)</td>
				</tr>
				<tr>
					<td>audio_cos</td>
					<td><input type="text" id="audio_cos" name="audio_cos" value="6"/></td>
					<td>sets the default audio/rtp packets Class of Service (COS)      (defaults to 6)</td>
				</tr>
				<tr>
					<td>video_tos</td>
					<td><input type="text" id="video_tos" name="video_tos" value="0x88"/></td>
					<td>sets the default video/rtp packets Type of Service (TOS)       (defaults to 0x88 = 10001000 = 136 = DSCP:100010 = AF41)</td>
				</tr>
				<tr>
					<td>video_cos</td>
					<td><input type="text" id="video_cos" name="video_cos" value="5"/></td>
					<td>sets the default video/rtp packets Class of Service (COS)      (defaults to 5)</td>
				</tr>
				<tr>
					<td>setvar</td>
					<td><input type="text" id="setvar" name="setvar" value=""/></td>
					<td> (MULTI-ENTRY) extra variables to be set on line initialization multiple entries possible (for example the sip number to use when dialing outside)</td>
				</tr>
				<tr>
					<td>disallow</td>
					<td><input type="text" id="disallow" name="disallow" value=""/></td>
					<td>(REQUIRED) (MULTI-ENTRY) First disallow all codecs, for example 'all'</td>
				</tr>
				<tr>
					<td>allow</td>
					<td><input type="text" id="allow" name="allow" value=""/></td>
					<td>(REQUIRED) (MULTI-ENTRY) Allow codecs in order of preference (Multiple lines allowed)</td>
				</tr>
				<tr>
					<td><button type="submit">Insert</button></td>
				</tr>
			</table>
		</form>
		<?php 
	case "update":
		?>
		
		
		<?php 
	break;
	case "do_update":
		
	break;
}
?>
