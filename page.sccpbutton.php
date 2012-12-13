<?php
include dirname(__FILE__)."/class.astinfo.php";

	$device = $_GET["device"];
	if ($_GET["action"]=="do_buttonconfig"){
		$decode = json_decode($_POST["data"]);
		//print_r($decode);
		sql("delete from buttonconfig where device='" . $device. "'","getAll",DB_FETCHMODE_ASSOC);
		foreach ($decode as $line){
			$instance = $line->instance;
			$type = $line->type;
			$name = $line->name;
			$options = $line->options;
			$vars = array(instance=>$instance,options=>$options,name=>$name,type=>$type,device=>$device );
			//print_r($vars); 
			sccp_add_button($vars);
		}
		die();
	}	
	$device = $_GET["device"];
	if (!$device){
		header("location: ?menu=pbxconfig&type=setup&display=sccpdevice");
	}
	$addable = array("empty","feature","service","speeddial");
	
	
	$lines = sccp_lines_list();
	$results = sql("SELECT * FROM buttonconfig where device='" . $device. "'","getAll",DB_FETCHMODE_ASSOC);
        if(is_array($results)){
                foreach($results as $result){
                        // check to see if we have a dept match for the current AMP User.
                                // return this item's dialplan destination, and the description
                                $allowed[] = $result;
                }
        }
?>

<script>
	var $optiontoedit;
	function removedupes(){
		$("#associatedlines option").each(function(){
			$("#unassociatedlines option[value='" + $(this).val() + "'] ").remove();
			//alert ($(this).val());
		});
	}
	function add_line(){
		if ($("#unassociatedlines option:selected").val()=="service" || $("#unassociatedlines option:selected").val()=="feature" || $("#unassociatedlines option:selected").val()=="empty"  ){
			$("#unassociatedlines option:selected").clone().appendTo('#associatedlines');
		}else{
		$("#unassociatedlines option:selected").appendTo('#associatedlines');
		}
	}
	function remove_line(){
		$("#associatedlines option:selected").appendTo('#unassociatedlines');
	}
	
	function line_up(){
		var $op = $('#associatedlines option:selected'),
        $this = $(this);
	    if($op.length){
	            $op.first().prev().before($op);
	    }
	}
	function line_down(){
		var $op = $('#associatedlines option:selected'),
        $this = $(this);
	    if($op.length){
	    	$op.last().next().after($op);
	    }
	}
	function buttons_save(){
		var buttons = new Array();
		var i = 0;
		$("#associatedlines option").each(function(){
			i++;
			var button = new Object();
			button.instance = i;
			var str =  $(this).val();
			var stra = str.split("|");
			button.type=stra[0];
			button.name=stra[1];
			button.options=stra[2];
			buttons.push(button);
		});
		$.post("?menu=pbxconfig&type=setup&display=sccpbutton&action=do_buttonconfig&device=<?=$device?>"
			, {data:JSON.stringify(buttons)}
		);		
		
	}
	function buttons_close(){
		window.location.href = "?menu=pbxconfig&type=setup&display=sccpdevice&action=update&device=<?=@$device?>";
	}
	function readoption($obj){
		$optiontoedit = $obj;
		$("#optionsval").val($optiontoedit.val());
		var button = new Object();;
		var str =  $optiontoedit.val();
		var stra = str.split("|");
		button.type=stra[0];
		button.name=stra[1];
		button.options=stra[2];

		switch (button.type){
			case "feature":
				$(".buttonbox").hide();
				$("#featurebox").show();
				$("#featurebox_label").val( button.name  );
				switch (button.options.split(",")[0]){
					case "cfwdAll":
					case "devstate":
						//$("#featurebox_feature").val( button.options.split(",")[0] );
						$("#featurebox_feature").val( button.options.split(",")[0] );
						$("#featurebox_other").val(button.options.split(",")[1]);
						$("#featurebox_other").show();
					break;
					default:
						$("#featurebox_feature").val( button.options  );
					break;
				}
				
			break;
			case "speeddial":
				$(".buttonbox").hide();
				$("#speeddialbox").show();
				$("#speeddialbox_label").val( button.name );
				$("#speeddialbox_number").val( (button.options).split(",")[0] );
				$("#speeddialbox_hint").val( (button.options).split(",")[1] );
				
			break;
			case "service":
				$(".buttonbox").hide();
				$("#servicebox").show();
				$("#servicebox_url").val ( button.options);
			break;
			case "line":
				$(".buttonbox").hide();
				$("#linebox").show();
				$("#linebox_number").val( button.name );
				$("#linebox_other").val( button.options );
			break;
		} 
					
	}
	function featurebox_feature_change($obj){
		switch($obj.val()){
			case "cfwdAll":
			case "devstate":
				$("#featurebox_other").show();
			break;
			default:
				$("#featurebox_other").hide();
				$("#featurebox_other").val("");
			break;
		}
	}
	function writeoption(){
		$optiontoedit.text( $("#optionsval").val().split("|")[0] + " - " + $("#optionsval").val().split("|")[1] + " - " +  $("#optionsval").val().split("|")[2]  ) ; 
		$optiontoedit.val( $("#optionsval").val() );
		
	}
	function resetdevice(){
		$.post("?menu=pbxconfig&type=setup&display=sccpdevice&action=do_reset&device=<?=@$device?>");
	}
	function restartdevice(){
		$.post("?menu=pbxconfig&type=setup&display=sccpdevice&action=do_restart&device=<?=@$device?>");
	}
	$(document).ready(function() {
		removedupes();
		$(".buttonbox").hide();
		$("#optionsblock input").change(function(){
			switch ($("#optionsval").val().split("|")[0]){
				case "feature":
					switch ($("#featurebox_feature").val()){
						case "cfwdAll":
						case "devstate":
							$("#optionsval").val("feature|" + $("#featurebox_label").val() + "|" + $("#featurebox_feature").val() + "," + $("#featurebox_other").val() );
						break;
						default:
							$("#optionsval").val("feature|" + $("#featurebox_label").val() + "|" + $("#featurebox_feature").val());
						break;
					}
				break;
				case "line":
					$("#optionsval").val("line|" + $("#linebox_number").val() + "|" + $("#linebox_other").val());
				break;
				case "speeddial":
					$("#optionsval").val("speeddial|" + $("#speeddialbox_label").val() + "|" + $("#speeddialbox_number").val() + "," + $("#speeddialbox_hint").val() );
				break;
				case "service":
					$("#optionsval").val("service|" + $("#servicebox_label").val() + "|" + $("#servicebox_url").val()  );
				break;
			}
		});
	});
	
	
</script>
		<button onclick="buttons_save();">save</button>
		<button onclick="buttons_close();">close</button>
		<button onclick="restartdevice();">restart</button>
		<button onclick="resetdevice();">reset</button>
		


<div>
	<div style="height:400px;">
		<div style="float:left;width:482px;" >
			Associated Items<br/>
			<select id="associatedlines" name="associatedlines" size=20 style="width:481px; align:right;" >
			  <?foreach ($results as $result){
			  ?>
			  	<option onclick="readoption($(this))" value="<?=$result["type"]?>|<?=$result["name"]?>|<?=$result["options"]?>"><?=$result["type"]?> - <?=$result["name"]?> <?=$result["options"]?></option>
			  <?
			  }
			  ?>
			</select>
		</div>
		<div style="width: 50px;text-align:center; float:left; height:341px; position:relative; ">
			<div style=" align:center; position:absolute; top:40%; height:50px; width:50px;">
			<button onclick=" remove_line();"> &gt; </button>
			<button onclick=" add_line();"> &lt; </button>
			<button onclick=" line_up();"> up </button>
			<button onclick=" line_down();"> down </button>
			</div>
		</div>
		<div style="float:left;width:482px">
			Unassociated Items<br/>
			<select id="unassociatedlines" name="unassociatedlines" size=20 style="width:481px;" >
			  <?php 
			  foreach ($addable as $key=>$value){
			  ?>	
			  <option onclick="readoption($(this))" value="<?=$value?>"><?=$value?></option>
			  <?php 
			  }
			  ?>
			  <?php 
			  foreach ($lines as $line){
			  ?>
			  <option onclick="readoption($(this))" value="line|<?=$line["name"]?>|<?=$line["description"]?>">line - <?=$line["name"]?> - <?=$line["description"]?> </option>
			  <?php 			  	
			  }
			  ?>
			</select>
		</div>
	</div>
	<div id="optionsblock" style="width:500px;">
		<div style="width:880px; display:block;">
			<br/>
			<label style="clear: both; float: left; width: 10%;"> Options Preview: </label>
			<input id="optionsval" type="text" name="options" value="" readonly size="50"/>
			<button onclick="writeoption()">update</button>
			<div id="featurebox" class="buttonbox">
				<label style="clear: both; float: left; width: 10%;">Label:</label> 
				<input type="text" name="Label" id="featurebox_label"   style="float: left; width: 55%;"/><br/>
				<label style="clear: both; float: left; width: 10%;">Feature:</label> 
				<select name="featurebox_feature" id="featurebox_feature" onchange="featurebox_feature_change($(this))"  style="float: left; width: 55%;">
					<option value="silent">Silent</option>
					<option value="dnd,silent">DND Silent</option>
					<option value="dnd,reject">DND Reject</option>
					<option value="monitor">Monitor</option>
					<option value="privacy">Private Call</option>
					<option value="cfwdAll">cfwdAll to...</option>
					<option value="devstate">custom devstate...</option>
				</select><br/>
				<label style="clear: both; float: left; width: 10%;">&nbsp;</label> <input type="text" value="" id="featurebox_other"  style="float: left; width: 55%;" />
			</div>
			<div id="servicebox" class="buttonbox">
				<label style="clear: both; float: left; width: 10%;">Label:</label><input style="float: left; width: 55%;" type="text" name="servicebox_label" id="servicebox_label" />
				<br/>
				<label style="clear: both; float: left; width: 10%;">Url:</label><input style="float: left; width: 55%;" type="text" name="servicebox_url" id="servicebox_url" />
			</div>
			<div id="speeddialbox" class="buttonbox">
				<label style="clear: both; float: left; width: 10%;">Label:</label> <input style="float: left; width: 55%;" type="text" name="speeddialbox_label" id="speeddialbox_label" />
				<br/>
				<label style="clear: both; float: left; width: 10%;">Number:</label> <input style="float: left; width: 55%;" type="text" name="speeddialbox_number" id="speeddialbox_number" />
				<br/>
				<label style="clear: both; float: left; width: 10%;">Hint:</label> <input style="float: left; width: 55%;" type="text" name="speeddialbox_hint" id="speeddialbox_hint" />
			</div>
			<div id="linebox" class="buttonbox">
				<label style="clear: both; float: left; width: 10%;">number:</label><input style="float: left; width: 55%;" type="text" name="linebox_number" id="linebox_number" readonly />
				<br/>
				<label style="clear: both; float: left; width: 10%;">other:</label> <input style="float: left; width: 55%;" type="text" name="linebox_other" id="linebox_other"  />
				<br/>
				
			</div>
		</div>
	</div>
</div>