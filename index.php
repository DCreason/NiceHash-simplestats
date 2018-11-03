<?php
// functions.php
function url_get_contents ($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
function pooptitle(){
	$arrayone = array("Mice","Dice","Lice","Slice","Ice","Heist","Rice","Price","Spice","Vice","Thrice","Vise","Gneiss","Splice",);
	$arraytwo = array("Bash","Cash","Sash","Dash","Ash","Brash","Clash","Crash","Cache","Slash","Mash",);
	return $arrayone[rand(0,count($arrayone)-1)]." ".$arraytwo[rand(0,count($arraytwo)-1)];
}
function html($str){
	if(false){
		echo(trim($str));
	} else {
		echo($str."\n");
	}
}
function fiats($addscript = false){
	if($addscript){
		html("<script>");
		html("function fiatchange(el){");
		html("	clearInterval(window.updatetime);");
		html("	ratedate = 10;");
		html("history.pushState({}, null, './?addr='+document.querySelector('#addr').innerHTML+'&fiat='+el.value); ");
		html("	checkrate();");
		html("}");
		html("</script>");
	}
	html("<p>Fiat: <select id='fiatbox' ".($addscript ? "onLoad='' onChange='fiatchange(this)' " : "").">");
		foreach(["USD","ISK","HKD","TWD","CHF","EUR","DKK","CLP","CAD","CNY","THB","AUD","SGD","KRW","JPY","PLN","GBP","SEK","NZD","BRL","RUB"] as $fiat){
			html("<option value='".$fiat."' ".(strtoupper($_GET["fiat"]) === $fiat ? "selected" : "").">".$fiat."</option>");
		}
	html("</select></p>");
}
if(isset($_GET["addr"]) && $_GET["addr"] != "" && isset($_GET["type"]) && $_GET["type"] == "json"){
	// json.php
	echo "{ \"nh\":".url_get_contents("https://api.nicehash.com/api?method=stats.provider.ex&from=0".(time()-60)."&addr=".$_GET["addr"]).", \"md5\":\"".md5(fread(fopen(__FILE__, "r"), 1048576))."\", \"under_construction\": ".((time() - filemtime(__FILE__)) < (15*60) ? 1 : 0).", \"timediff\":\"".(time() - filemtime(__FILE__))."\" }";
} elseif(isset($_GET["addr"]) && $_GET["addr"] != "" && isset($_GET["type"]) && $_GET["type"] == "payments"){
	echo url_get_contents("https://api.nicehash.com/api?method=stats.provider&addr=".$_GET["addr"]);
} else if(isset($_GET["noscript"]) && isset($_GET["addr"])) { // no script
	$api = json_decode(url_get_contents("https://api.nicehash.com/api?method=stats.provider.ex&from="."&addr=".$_GET["addr"]));
	$result = $api->result->current;
	foreach($result as $algo){
		if($algo->data[0] != json_decode("{}")){
			html($algo->name);
		}
	}
}else {
	html("<!DOCTYPE html>");
	html("<html style='text-align: center;'>");
	html("<head>");
	html("	<title>".pooptitle()."</title>");
	html("<script>");
	html("var numerrors = 0;");
	html("window.onerror = function(msg){");
	html("	if(numerrors > 3){");
	html("		location.reload();");
	html("	} else {");
	html("		numerrors++;");
	html("		console.warn('ERROR:');");
	html("		console.warn(msg);");
	html("		checkrate();");
	html("		return true;");
	html("	}");
	html("};");
	html("</script>");
	html("<style>");
	html("	.progress-bar{");
	html("		background-color:#757575;");
	html("		height:100%;");
	html("		position:absolute;");
	html("		line-height:inherit;");
	html("	}");
	html("	.progress-container{");
	html("		width:100%;");
	html("		height:1.5em;");
	html("		position:relative;");
	html("		background-color:#f1f1f1");
	html("	}");
	html("	.hidden{");
	html("		visibility: hidden;");
	html("	}");
	html("</style>");
	html("</head>");
	html("<body>");
	html("<img id='under_construction' src='https://upload.wikimedia.org/wikipedia/en/1/1d/Page_Under_Construction.png' style='height:10vw; width:20vw;' class='".((time() - filemtime(__FILE__)) < (21600 + 15*60) ? "" : "hidden")."'></img>");
	html("<div style='padding-top: 0%;'>");
	html("<h3>Nice Hash stats kept simple.</h3>");
	if(isset($_GET["addr"]) && $_GET["addr"] != ""){
		html("<h4 id=\"addr\">".$_GET["addr"]."</h4>");
		fiats(true);
		html("<table style='margin: auto;'>");
		html("<th>Algorithm</th>");
		html("<th>Profitability (BTC)</th>");
		html("<th>Profitability (Fiat)</th>");
		html("<th>Speed</th>");
		html("<tbody id='youl'>");
		html("</tbody>");
		html("</table>");
		html("<p id='unpaid' style='margin: 0px 0px 0px 0px;'>Loading...</p>");
		html("<table style='margin: auto;'>");
		html("<h3 style='margin: 0px;'>Payments</h3>");
		html("<th>Amount(BTC)</th>");
		html("<th>Amount(Fiat)</th>");
		html("<th>Time</th>");
		html("<tbody id='paymentsbox'>");
		html("</tbody>");
		html("</table>");
		html("<script>");
		html("window.onload = function(){window.ratedate = 10; checkrate();};");
		html("function getdata(addr){");
		html("	var oReq = new XMLHttpRequest();");
		html("	oReq.addEventListener(\"load\", function(){dontdoonlytry(this.responseText)});");
		html("	oReq.open(\"GET\", '".$_SERVER['PHP_SELF']."'+\"/?from=0&addr=\"+addr+\"&type=json\");");
		html("	oReq.send();");
		html("}");
		html("function checkrate(){");
		html("		window.fiat = document.querySelector('#fiatbox').value;");
		html("		if(ratedate < new Date().getTime() - 60000){");
		html("			var oReq = new XMLHttpRequest();");
		html("			oReq.addEventListener(\"load\", function(){");
		html("				var json = JSON.parse(this.responseText);");
		html("				window.exrate = json[fiat]['15m'];");
		html("				ratedate = new Date().getTime();");
		html("				getdata(document.querySelector(\"#addr\").innerHTML);");
		html("			});");
		html("			oReq.open(\"GET\", \"https://blockchain.info/ticker?cors=true\");");
		html("			oReq.send();");
		html("		} else {");
		html("			getdata(document.querySelector(\"#addr\").innerHTML);");
		html("		}");
		html("}");
		html("function dontdoonlytry(data){"); // You're right, this was added as an after thought
		html("	try{");
		html("		api(data);");
		html("	}");
		html("	catch(err){");
		html("		if(err){");
		html("			console.warn(err);");
		html("			console.warn('Ouch!');");
		html("			setTimeout(dontdoonlytry, 5000);");
		html("		}");
		html("	}");
		html("}");
		html("var mdfive;");
		html("function api(data){");
		html("	var text = JSON.parse(data);");
		html("	var json = text.nh;");
		html("	if(!mdfive){");
		html("		mdfive = text.md5;");
		html("	} else {");
		html("		if(mdfive !== text.md5){");
		html("			window.location.reload();");
		html("		}");
		html("	}");
		html("	document.querySelector('#under_construction').setAttribute('class', (text.under_construction ? '' : 'hidden')); ");
		html("	var totalprofitability = 0;");
		html("	if(typeof json['result']['error'] == 'string'){");
		html("	//	document.querySelector('#youl').innerHTML = \"<li>json['result']['error']</li>\";");
		html("      checkrate();");
		html("	} else {");
		html("	var algos = json.result.current;");
		html("	document.querySelector(\"#youl\").innerHTML = \"\";");
		html("	var unpaid = 0;");
		html("	algos.forEach(function(algo, index, arr){");
		html("		if(algo[\"data\"][1] !== '0'){");
		html("			unpaid += parseFloat(algo[\"data\"][1]);");
		html("		}");
		html("		if(algo[\"data\"][0][\"a\"]){");
		html("			var profitability = algo[\"data\"][0][\"a\"]*algo[\"profitability\"];");
		html("			totalprofitability += profitability;");
		html("			document.querySelector(\"#youl\").innerHTML += \"<tr><td>\"+algo[\"name\"]+\"</td><td>\"+profitability.toFixed(6)+\" BTC/day</td><td>\"+(profitability * exrate).toFixed(2) +\" \"+window.fiat+\"/day</td><td>\"+ algo[\"data\"][0][\"a\"] + \" \"+algo[\"suffix\"] +\"/s</td></tr>\";");
		html("		}");
		html("		if(index == arr.length -1){");
		html("			console.log((totalprofitability*exrate).toFixed(2)+' '+window.fiat+'/day')");
		html("			document.querySelector(\"#youl\").innerHTML += '<tr><td>Total</td><td>'+totalprofitability.toFixed(8)+' BTC/day</td><td>'+(totalprofitability*exrate).toFixed(2)+' '+window.fiat+'/day</td><td></td></tr>';");
		html("			document.querySelector(\"#unpaid\").innerHTML = \"Unpaid balance: \"+unpaid.toFixed(8)+\" | \"+(unpaid*exrate).toFixed(2)+\" \"+window.fiat;");
		html("			window.updatetime = setTimeout(checkrate, 30000);");
		html("			getpaid();");
		html("		}");
		html("	});");
		html("	}");
		html("}");
		html("function getpaid(){");
		html("	var oReq = new XMLHttpRequest();");
		html("	oReq.addEventListener(\"load\", function(){");
		html("		document.querySelector('#paymentsbox').innerHTML = '';");
		html("		JSON.parse(this.responseText)['result']['payments'].forEach(function(val, i, arr){");
		html("			document.querySelector('#paymentsbox').innerHTML += '<tr><td>'+(val.amount)+'</td><td>'+(val.amount*exrate).toFixed(2)+' '+fiat+'</td><td>'+new Date(val.time).toDateString()+' '+new Date(val.time).toLocaleTimeString()+'</td></tr>';");
		html("		});");
		html("	});");
		html("	oReq.open(\"GET\", \"./?type=payments&addr=\"+addr.innerHTML);");
		html("	oReq.send();");
		html("}");
		html("</script>");
	} else {
		html("<h3>Input your deposit address to see stats</h3>");
		html("<form id='firm' method='GET' action='./' >");
		html("<input type='text' id='addrbox' placeholder='Deposit address' name='addr'/>");
		html("<br/>");
		fiats();
		html("<input type='button' value='Go!' id='go' />");
		html("</form>");
		html("<script>");
		html("document.querySelector('#go').addEventListener('click', gotem, true);");
		html("function gotem(bool){");
		html("	if(bool){");
		html("		window.location=window.location + '?addr='+document.querySelector('#addrbox').value+'&fiat='+document.querySelector('#fiatbox').value;");
		html("	}");
		html("}");
		html("</script>");
	}
	html("</div>");
	html("</body>");
	html("</html>");
}
?>
