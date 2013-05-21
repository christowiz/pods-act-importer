
function showoptions(){
		var k = document.getElementById('importop').value;
		if(k =='CSV_file'){
			document.getElementById('csv_in').style.display="";
			document.getElementById('sql_in').style.display="none";
			document.getElementById('Import').style.display="";
		}
		else if(k =='MySql_Table') {
			document.getElementById('csv_in').style.display="none";
			document.getElementById('sql_in').style.display="";
			document.getElementById('Import').style.display="";
		} else {
				document.getElementById('csv_in').style.display="none";
			document.getElementById('sql_in').style.display="none";
			document.getElementById('Import').style.display="none";
			
			}
	}
	
	
function wherecon(){
		var k = document.getElementById('where1').value;
		if(k =='id'){
			document.getElementById('trpermalink').style.display="";
			document.getElementById('trid').style.display="none";
			
		}
		else if(k =='permalink') {
			document.getElementById('trpermalink').style.display="none";
			document.getElementById('trid').style.display="";
			
		}
	}	
	
function showidmap()
{
	
  if(document.getElementById('idforce').checked) {
     document.getElementById('id').style.display="";}
  else{
    document.getElementById('id').style.display="none";}
}

function showwhere()
{
	
  if(document.getElementById('update').checked) {
     document.getElementById('where').style.display="";
	 document.getElementById('drop_table').style.display="none";
	 document.getElementById('where1').value="id";
	 document.getElementById('trid').style.display="none";
	  var selects = document.getElementsByTagName('select');
    var len = selects.length;
    for(var i=3; i<len; i++){
        selects[i].selectedIndex=0;
    }
	 }
  else{
    document.getElementById('where').style.display="none";
	document.getElementById('trpermalink').style.display="";
	document.getElementById('trid').style.display="";
	document.getElementById('drop_table').style.display="";}
}

function showpost_type(){
		var op = document.getElementById('tabella_msql').value;
		if ( op.indexOf( "posts" ) > -1 ){
			document.getElementById('posts_t').style.display="";
			
		
		} else {
				document.getElementById('posts_t').style.display="none";
		
			
			}
	}	


function addconstant(myval){
	var a = document.getElementById('h1').value;
	var aa = document.getElementById('h2').value;

	for(var i=0;i<aa;i++){ 
		var b = document.getElementById(myval).value;
		if(b=='add_a_constant'){
			document.getElementById('textbox'+myval).style.display="";
		}
		else{
			document.getElementById('textbox'+myval).style.display="none";
		}
	}
}


// Function for check file exist

function file_exist_pod(){

	if(document.getElementById('tabella').value==''){
		return false;
	}
	else{
		return true;
	}
}

// Code added at version 1.0.2 by fredrick

// Function for import csv

function import_csv_pod(){
	var header_count = document.getElementById('h2').value;
	var array = new Array();
	var val1;
	val1 = 'Off';
	for(var i=0;i<header_count;i++){
	
	}

   	 return true;

}
