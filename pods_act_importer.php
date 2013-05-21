<?php
/*
*Plugin Name: Pods ACT Importer
*Description: A plugin that helps to import data in Pods Advanced Content Type tables from a CSV file or a MySql Table.
*Version: 1.0.0
*Author: Marco Emanuele Muraca (memuraca@gmail.com) -  Original Plugin (Wp Ultimate CSV Importer) by Fredrick SujinDoss.M
*
* Copyright (C) 2012 Fredrick SujinDoss.M (email : fredrickm@smackcoders.com)
*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
* @link http://www.smackcoders.com/category/free-wordpress-plugins.html

***********************************************************************************************
*/



function generateSeoTitle($title='') { //permalink generator      
 $title = strtolower($title);    $zio = array(  'Ì',  'Í',  'ß',  'ö',  'Ö',  ' ',   '(',   ')',   '%',   '+',   '&',   '.',   ',',   ':',   '/',   '?',   '’',   '"',   '“',   '”',   '–',   'À',   'È',   'É',   'Ù',   'Ú',   "'",   '"',  'ì',  'í',  'è',  'é',  'à',  'ù',  "'",  '"',  '!',  '#039;',  '&amp;',  'Ä',  'ä',  'ü',  'Ü',  'ò',  'ó',  'Ò',  'Ó',  'Á',  'á',  'ñ',  '’');   
 $ziosanato = array(  'i',  'i',  'ss',  'oe',  'oe',  '-',   '',   '',   '-',   '',   'e',   '',   '-',   '-',   '-',   '-',   '-',   '',   '',   '',   '-',   'a',   'e',   'e',   'u',   'u',   '-',   '',  'i',  'i',  'e',  'e',  'a',  'u',  "",  '',  '',  '-',  'e',  'a',  'a',  'u',  'u',  'o',  'o',  'o',  'o',  'a',  'a',  'gn',  '-');   
 $title = str_replace($zio,$ziosanato,$title);  $title = preg_replace('/[^a-zA-Z0-9_ \-()\/%-&]/s', '', $title);  
 $ogre = array('----','---','--');  
 $akuma = array('-','-','-');  
 $title = str_replace($ogre,$akuma,$title);     
 $title = preg_replace('/-$/', '', $title);      
 if (sizeof($title) > 0)      
 {          
 return $title;      }      
 else          
 return '';  } 


$upload_dir = wp_upload_dir();
$importdir  = $upload_dir['basedir']."/imported_csv/";
if(!is_dir($importdir))
{
        wp_mkdir_p($importdir);
}

// Global variable declaration
global $data_rows_pod;
$data_rows_pod = array();
global $headers_pod ;
$headers_pod = array();
global $defaults_pod;
global $wpdb;
global $keys_pod;
$keys_pod = array();
global $delim_pod;
global $posts_t_cat;
global $msql_table;
global $importop;

$importop = empty($_POST['importop']) ? '' : $_POST['importop'];
$tabella_msql = empty($_POST['tabella_msql']) ? '' : $_POST['tabella_msql'];
$posts_t_cat = empty($_POST['posts_t_cat']) ? '' : $_POST['posts_t_cat'];
$delim_pod = empty($_POST['delim']) ? '' : $_POST['delim'];
$tabella = empty($_POST['tabella']) ? '' : $_POST['tabella'];
//Columns from selected table
if (!empty($tabella)) {
$merde = $wpdb->get_results( "SHOW COLUMNS FROM ".$tabella,  ARRAY_A);
foreach($merde as $merda){
$keys_pod[$merda['Field']] = $merda['Field'];
}

$defaults_pod = array();
foreach($keys_pod as $val){
	$defaults_pod[$val]=$val;}
	
	
}
	
	
	
// Admin menu settings
function wp_pod_csv_importer() {  
	add_menu_page('Settings Importatore CSV', 'Pods ACT Importer', 'manage_options',  
	       'upload_csv_file_pod', 'upload_csv_file_pod');
}  



function LoadWpScriptPod()
{
        wp_register_script('wp_csvpod_scripts', plugins_url( 'pods_act_importer.js', __FILE__ ), array("jquery"));
        wp_enqueue_script('wp_csvpod_scripts');
}
add_action('admin_enqueue_scripts', 'LoadWpScriptPod');

#       -- Code ends here --

add_action("admin_menu", "wp_pod_csv_importer");  



// CSV File Reader
function csv_file_data_pod($file,$delim_pod)
{
	ini_set("auto_detect_line_endings", true);
	global $data_rows_pod;
	global $headers_pod;
	global $delim_pod;
	
        $c = 0;
        $resource = fopen($file, 'r');
        while ($keys_pod = fgetcsv($resource,'',"$delim_pod",'"')) {
            if ($c == 0) { $headers_pod = $keys_pod;} 
                array_push($data_rows_pod, array_map('utf8_encode',$keys_pod));
            
            $c ++;
        }
        fclose($resource);
	ini_set("auto_detect_line_endings", false);
}

// Move file
function move_file_pod()
{
    
    $upload_dir = wp_upload_dir();
    $uploads_dir  = $upload_dir['basedir']."/imported_csv/";
    if ($_FILES["csv_import"]["error"] == 0) {
        $tmp_name = $_FILES["csv_import"]["tmp_name"];
        $name = $_FILES["csv_import"]["name"];
        move_uploaded_file($tmp_name, "$uploads_dir/$name");
    }
}

// Remove file
function fileDeletePod($filepath,$filename) {
	$success = FALSE;
	if (file_exists($filepath.$filename)&&$filename!=""&&$filename!="n/a") {
		unlink ($filepath.$filename);
		$success = TRUE;
	}
	return $success;	
}

// Mapping the fields and upload data's
function upload_csv_file_pod()
{
	global $wpdb;
	global $headers_pod;
	global $data_rows_pod;
	global $tabella;
	global $defaults_pod;
	global $keys_pod;
	global $custom_array_pod;
	global $delim_pod;
	global $posts_t_cat;
	global $msql_table;
	global $importop;

	$custom_array_pod = array();
	
  
	if(isset($_POST['importop']))
	{
		
		
		//
	   //
	   //CSV Form 
	   //
	   //
		
		if ( $_POST['importop'] == "CSV_file" ) {
			
			
	$upload_dir = wp_upload_dir();
    $importdir  = $upload_dir['basedir']."/imported_csv/";
		
			
		csv_file_data_pod($_FILES['csv_import']['tmp_name'],$delim_pod);
		move_file_pod();
		?>

    
		<?php if ( count($headers_pod)>=1 &&  count($data_rows_pod)>=1 ){?>
		
        <div style="float:left;min-width:45%">
        <p></p>
		<form class="add:the-list: validate" method="post" onsubmit="return import_csv_pod();">
		
          <p>	 <label><input name="firsth" type="checkbox"  value="firsth" /><strong>First csv row = HEADERS? (ignore first row when import)  </strong></label></p>
      
      
      
     <p style="color:#FF0000" id="drop_table">  <label><input name="drop_table" type="checkbox"  value="drop_table" /><strong> (TRUNCATE TABLE) Delete all the data before import </strong></label> </p>
          <p>  <label><input name="update" type="checkbox"  id="update" onclick="showwhere()" /><strong> UPDATE Table </strong></label> </p>
        
		<div id="where" style="display:none">	
             <h3>WHERE CONDITION</h3>
            
               <table style="font-size:12px;" >
			
			 <tr>
			    <td>
				 <select  name="where1" id="where1" class ='uiButton' onchange="wherecon()" >
				
			
					<option value ="id">id</option>
			   		<option value ="permalink">permalink</option>
				
			    </select>
			    </td>
			    <td>
			    <select  name="where2" id="where2" class ='uiButton' >
				
			      <?php 
				foreach($headers_pod as $key=>$value){ 
			    ?>
					<option value ="<?php print($key);?>"><?php print($value);?></option>
			    <?php }
		   	    ?>
				
			    </select>
			   
			  </td>
			 </tr>
            </table>
            
            </div>
        
			<h3>Mapping the Fields</h3>
            

			<div id='display_area'>
			<?php $cnt =count($defaults_pod)+2; $cnt1 =count($headers_pod); ?>
			<input type="hidden" id="h1" name="h1" value="<?php echo $cnt; ?>"/>
			<input type="hidden" id="h2" name="h2" value="<?php echo $cnt1; ?>"/>
			<input type="hidden" id="delim" name="delim" value="<?php echo $_POST['delim']; ?>" />
            <input type="hidden" id="tabella" name="tabella" value="<?php echo $_POST['tabella']; ?>" />
            
			<input type="hidden" id="header_array" name="header_array" value="<?php print_r($headers_pod);?>" />
			
            
            
            <table style="font-size:12px;">
            <tr> 
            <th><?php echo $_POST['tabella']; ?> columns</th>
            <th>CSV file columns</th>
			<th></th>

            </tr>
            
			 <?php
			  $count = 0;
			  foreach($defaults_pod as $key1=>$value1) {
			if ($key1 == "id"){
			?>
			
                  <tr id="tr<?php print($value1);?>">
			    <td>
				<label>ID</label>
			    </td>
			    <td>
			    <select  name="id" id="id" class ='uiButton'  style="display: none" >
				<option id="select" name="select">-- Select --</option>
			    <?php 
				foreach($headers_pod as $key=>$value){ 
			    ?>
					<option value ="<?php print($key);?>"><?php print($value);?></option>
			    <?php }
		   	    ?>
				
			    </select>
			   
			  </td>
              <td>
              <label><input name="idforce" type="checkbox" id="idforce" onclick="showidmap()" /><strong>Select id from the CSV file? Rows with invalid or duplicate ID values will not be imported!!!!</strong></label>
              </td>
			 </tr>
     
			<?php } else if  ($key1 == "permalink"){ ?>
            
                      <tr id="tr<?php print($value1);?>">
			    <td>
				<label>Permalink</label>
			    </td>
			    <td>
			    <select  name="<?php print($value1);?>" id="<?php print($value1);?>" class ='uiButton'  >
				<option id="select" name="select">-- Select --</option>
			    <?php 
				foreach($headers_pod as $key=>$value){ 
			    ?>
					<option value ="<?php print($key);?>"><?php print($value);?></option>
			    <?php }
		   	    ?>
				
			    </select>
			   
			  </td>
               <td>
               If not selected it will be automatically generated based on field <strong>name</strong>
              </td>
			 </tr>
            
            
            <?php } else if  ($key1 == "created" || $key1 == "modified") { ?>
            
            
                      <tr>
			    <td>
				<label><?php print($key1);?></label>
			    </td>
			    <td>
			    <select  name="<?php print($value1);?>" id="<?php print($value1);?>" class ='uiButton'  onchange="addconstant(this.id);">
				<option id="select" name="select">-- Select --</option>
			    <?php 
				foreach($headers_pod as $key=>$value){ 
			    ?>
					<option value ="<?php print($key);?>"><?php print($value);?></option>
			    <?php }
		   	    ?>
				<option value ="add_a_constant">add_a_constant</option>
			    </select>
			   
			  </td>
               <td>
               <input type="datetime" id="textbox<?php print($value1); ?>" name="textbox<?php print($value1); ?>" style="display:none;"/> If not selected it will be automatically generated based on <strong>Current Time</strong>.
              </td>
			 </tr>
  
            
       
              <?php } else { ?>
             <tr>
			    <td>
				<label><?php print($key1);?></label>
			    </td>
			    <td>
			    <select  name="<?php print($value1);?>" id="<?php print($value1);?>" class ='uiButton' onchange="addconstant(this.id);" >
				<option id="select" name="select">-- Select --</option>
			    <?php 
				foreach($headers_pod as $key=>$value){ 
			    ?>
					<option value ="<?php print($key);?>"><?php print($value);?></option>
			    <?php }
		   	    ?>
				<option value ="add_a_constant">add_a_constant</option>
			    </select>
			   
			  </td>
               <td>
                  <input type="text" id="textbox<?php print($value1); ?>" name="textbox<?php print($value1); ?>" style="display:none;"/>
              </td>
			 </tr>
          
             <?php }
		   	    ?>
            
             <?php
			   $count++; } 
			 ?>
             	
 
            </table>
            
            
            
			</div><br/> 
			<input type='hidden' name='filename' id='filename' value="<?php echo($_FILES['csv_import']['name']);?>" />
			<input type='submit' name= 'post_csv' id='post_csv' value='Import' />
		</form>
		</div>
		<div style="min-width:45%;">
			
		</div>
	<?php
		}
	
		else { ?>
		<div style="font-size:16px;margin-left:20px;">Your CSV file cannot be processed. It may contains wrong delimiter or please choose the correct delimiter.
		</div><br/>
		<div style="margin-left:20px;">
		<form class="add:the-list: validate" method="post" action="">
			<input type="submit" class="button" name="Import Again" value="Import Again"/>
		</form>
		</div>
		<div style="margin-left:20px;margin-top:30px;">
			<b>Note :-</b>
			<p>1. Your CSV should contain "," or ";" as delimiters.</p>
			<p>2. In CSV, tags should be seperated by "," to import mutiple tags and categories should be seperated by "|" to import multiple categories.</p>
		</div>
	

	
	<?php	}
	} 
		//
	   //
	   //Mysql Form
	   //
	   //
	   else if ( $_POST['importop'] == "MySql_Table" )  { ?>
		  
		  
		  
 <?php 
 
$tabella_msql = empty($_POST['tabella_msql']) ? '' : $_POST['tabella_msql'];
$posts_t_cat = empty($_POST['posts_t_cat']) ? '' : $_POST['posts_t_cat'];
$max_meta = array();

//get fields from mysql table
$cacche = $wpdb->get_results( "SHOW COLUMNS FROM ".$tabella_msql,  ARRAY_A);
foreach($cacche as $cacca){
$mysqlkeys[$cacca['Field']] = $cacca['Field'];
}


$defaults_podmy = array();
foreach($mysqlkeys as $valy){
$defaults_podmy[$valy]=$valy;}

//case table is wp_posts
if(strstr($tabella_msql, "posts")) {

unset(	$defaults_podmy['post_date_gmt'],
		$defaults_podmy['to_ping'],
		$defaults_podmy['pinged'],
		$defaults_podmy['post_modified_gmt'],
		$defaults_podmy['post_content_filtered'],
		$defaults_podmy['menu_order'],
		$defaults_podmy['post_mime_type']
		);

	
//search for the post with max custom fields
$numeri_meta = $wpdb->get_results("SELECT keyx.id, keyx.post_type, COUNT( keyu.post_id ) AS conteggio
						FROM $wpdb->posts keyx
						LEFT JOIN $wpdb->postmeta keyu 
						ON keyu.post_id = keyx.id
						WHERE keyx.post_type LIKE  '$posts_t_cat'
						AND keyx.post_status LIKE  'publish'
						AND keyu.meta_key NOT LIKE '\_%' 
						GROUP BY keyx.id");
	 

foreach ($numeri_meta as $numero_meta) {
$max_meta[$numero_meta->id] = $numero_meta->conteggio; }
$post_id_max_meta = array_search(max($max_meta), $max_meta);
//list the custom fields of the post with max custom fields
$custom_field_keys = get_post_custom_keys($post_id_max_meta);
foreach ( $custom_field_keys as $keyu => $valueu ) {
$valuet = trim($valueu);
if ( '_' == $valuet{0} )
continue;
$defaults_podmy['customxxx'.$valueu] = $valueu  ; }

}

 

 ?>
 

 <div style="float:left;min-width:45%">
        <p></p>
		<form class="add:the-list: validate" method="post" onsubmit="return import_mysql();">
      
     <p style="color:#FF0000" id="drop_table">  <label><input name="drop_table" type="checkbox"  value="drop_table" /><strong> (TRUNCATE TABLE) Delete all the data before import </strong></label> </p>
          <p>  <label><input name="update" type="checkbox"  id="update" onclick="showwhere()" /><strong> UPDATE Table </strong></label> </p>
        
		<div id="where" style="display:none">	
             <h3>WHERE CONDITION</h3>
            
               <table style="font-size:12px;" >
			
			 <tr>
			    <td>
				 <select  name="where1" id="where1" class ='uiButton' onchange="wherecon()" >
				
			
					<option value ="id">id</option>
			   		<option value ="permalink">permalink</option>
				
			    </select>
			    </td>
			    <td>
			    <select  name="where2" id="where2" class ='uiButton' >
				
			    <?php 
				  foreach($defaults_podmy as $key1=>$value1){
			    ?>
					<option value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php }
		   	    ?>
				
			    </select>
			   
			  </td>
			 </tr>
            </table>
            
            </div>
            <h3>Mapping the Fields</h3>
           
			<div id='display_area'>
			<?php $cnt =count($defaults_podmy)+2; $cnt1 =count($keys_pod); ?>
			<input type="hidden" id="h1" name="h1" value="<?php echo $cnt; ?>"/>
			<input type="hidden" id="h2" name="h2" value="<?php echo $cnt1; ?>"/>
			<input type="hidden" id="posts_t_cat" name="posts_t_cat" value="<?php echo $_POST['posts_t_cat']; ?>" />
            <input type="hidden" id="tabella_msql" name="tabella_msql" value="<?php echo $_POST['tabella_msql']; ?>" />
             <input type="hidden" id="tabella" name="tabella" value="<?php echo $_POST['tabella']; ?>" />

            
           	<table style="font-size:12px;">
			  <tr> 
            <th><?php echo $_POST['tabella']; ?> </th>
            <th><?php echo $_POST['tabella_msql']; ?> </th>
			<th> </th>

            </tr>
            
             <?php
			  $count = 0;
			  foreach($keys_pod as $key=>$value){ 
			
			 if ($key == "created" || $key == "modified") { ?>
				 <tr id="tr<?php print($value);?>">
			    <td >
				<label><?php print($value);?></label>
			    </td>
			    <td>
			    <select  name="<?php print($value);?>" id="<?php print($value);?>" class ='uiButton' onchange="addconstant(this.id);">
				<option id="select" name="select">-- Select --</option>
			    <?php 
				 
				  foreach($defaults_podmy as $key1=>$value1){
					  if ($key1 == $value) {
				?>
					<option selected="selected" value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php } 
					else if ($key1 == "post_date" && $value == "created") {
					
			    ?>
					<option selected="selected" value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php 
					 }
				
					else if ($key1 == "post_modified" && $value == "modified") {
					
			    ?>
					<option selected="selected" value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php 
					 }
				
				
				else {
			    ?>
					<option value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php }
				}
		   	    ?>
				<option value ="add_a_constant">add_a_constant</option>
			    </select>
			   
			  </td>
                 <td>
               
                 <input type="datetime" id="textbox<?php print($value); ?>"  name="textbox<?php print($value); ?>" style="display:none;"/> <label><?php if (($value1 != "modified" &&  $value1 != "created") && !strstr($tabella_msql, "posts") ) { ?><input name="unixtt<?php print($value); ?>" type="checkbox"  id="unixtt<?php print($value); ?>"  /><strong> Format Unixtime </strong></label> 
                   <?php } ?>
                - If not selected it will be automatically generated based on <strong>Current Time</strong>
              </td>
			 </tr> 
				 
				 
			<?php	 }
			 
			 else {
			 ?>
			 <tr id="tr<?php print($value);?>">
			    <td >
				<label><?php print($value);?></label>
			    </td>
			    <td>
			    <select  name="<?php print($value);?>" id="<?php print($value);?>" class ='uiButton' onchange="addconstant(this.id);">
				<option id="select" name="select">-- Select --</option>
			    <?php 
				  foreach($defaults_podmy as $key1=>$value1){
			   	  if ($value1 == $value) {
				?>
					<option selected="selected" value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php } 
				else  if ($value == 'name' && $value1 == 'post_title') {
				?>
					<option selected="selected" value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php }
				else  if ($value == 'permalink' && $value1 == 'post_name') {
				?>
					<option selected="selected" value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php }
					else {
			    ?>
					<option value ="<?php print($key1);?>"><?php print($value1);?></option>
			    <?php }}
		   	    
				if ($value != "permalink") { ?>
				<option value ="add_a_constant">add_a_constant</option>
			     <?php }  ?>
                </select>
			   
			  </td>
                 <td>
                 <?php if ($value != "permalink") { ?>
                  <input type="text" id="textbox<?php print($value); ?>" name="textbox<?php print($value); ?>" style="display:none;"/>
               <?php } else { ?>
                  If not selected it will be automatically generated based on field <strong>name</strong> 
                  <?php } ?>
              </td>
			 </tr>

            
             <?php  }
			   $count++; } 
			?>
             	</table>
            
			</div><br/> 
			
			<input type='submit' name= 'post_csv' id='post_csv' value='Import' />
		</form>
        
       
        
		</div>
        <?php 
		  	 
	 }
		}
	
	
	
	else if(isset($_POST['post_csv']))
	{
		
		/////
		/////
		///// CSV IMPORT
		///// 
		/////
		if(isset($_POST['filename']))
		{
        	$upload_dir = wp_upload_dir();
	        $dir  = $upload_dir['basedir']."/imported_csv/";
		csv_file_data_pod($dir.$_POST['filename'],$delim_pod);
		
		if(isset($_POST['drop_table'])){
				global $wpdb;
				$wpdb->query("TRUNCATE TABLE $tabella");
			}
		
			if(isset($_POST['firsth'])){
				$headers_pod = array_shift($data_rows_pod);
			}
			
		foreach($_POST as $postkey=>$postvalue){
	
			
			if ($postvalue != "") {
				
			
			if (strpos($postkey,"textbox") !== false) {
				$textboxkey = str_replace("textbox", "", $postkey);
				$texto[$textboxkey] = $postvalue;
				}
		
			else if($postvalue != '-- Select --' ){
		
			 if( $postvalue != 'add_a_constant') {
				$ret_array[$postkey]=$postvalue;
			}}}
		}
	
	
		
		foreach($data_rows_pod as $key => $value){
			
		
			unset($custom_array_pod);
			if(isset($_POST['update'])){
			$wherray[$_POST['where1']] = $value[$_POST['where2']];
			}
			
			
			foreach($ret_array as $koso => $valuso){
						$new_post[$koso] = $value[$valuso];	}
			
		
			for($inc=0;$inc<count($value);$inc++){
			   foreach($keys_pod as $k => $v){
			  
			  if(array_key_exists($v,$texto)){
				$custom_array_pod[$v] = $texto[$v];}
			
			
			
			if(array_key_exists($v,$new_post)){
			$custom_array_pod[$v] = $new_post[$v];
				}   }
			   
			if(!isset($_POST['update'])) {
			if (array_key_exists('permalink',$keys_pod)) {
			if (!array_key_exists('permalink',$custom_array_pod)) { 
			$custom_array_pod['permalink'] = generateSeoTitle(($custom_array_pod['name']));} 		
			else
			{$custom_array_pod['permalink'] = generateSeoTitle(($custom_array_pod['permalink']));}
			 }
			
			
			if (array_key_exists('created',$keys_pod) && !array_key_exists('created',$custom_array_pod)) { 
			$custom_array_pod['created'] = current_time('mysql', 1);}	 
			}
			
			if (array_key_exists('modified',$keys_pod)  && !array_key_exists('modified',$custom_array_pod)) { 
			$custom_array_pod['modified'] = current_time('mysql', 1);}  
			
			
			}
			
			if(isset($_POST['update'])){

			
$wpdb->update($tabella, $custom_array_pod, $wherray);

			}	else {
$wpdb->insert( $tabella, $custom_array_pod );	}
				
				}
			
				}

//	
//
//mysql import
//
//	
if(isset($_POST['tabella_msql'])) {
$posts_t_cat = $_POST['posts_t_cat'];		
$tabella_msql = $_POST['tabella_msql'];			
				
		if(isset($_POST['drop_table'])){
				global $wpdb;
				$wpdb->query("TRUNCATE TABLE $tabella");
			}
		
	
			
		foreach($_POST as $postkey=>$postvalue){
			if ($postvalue != "") 
			
			{
			if (strpos($postkey,"textbox") !== false) {
				$textboxkey = str_replace("textbox", "", $postkey);
				$texto[$textboxkey] = $postvalue;
				
				}
				
				else if (strpos($postvalue,"customxxx") !== false) 
				{
				$customfikey = str_replace("customxxx", "", $postvalue); 
				$customizzati[$customfikey] = $postkey;
			
			
				
				} 

				else if($postvalue != '-- Select --'){
				if($postvalue != 'add_a_constant'){
				$ret_array[$postkey]=$postvalue;
				
				
			}}
		}}
		
		
		
foreach($keys_pod as $v)
		{
		if(array_key_exists($v, $ret_array))
		{
		$mysql_match[$v] = $ret_array[$v];
					
		}
		}






$ganzo = array();
foreach ($mysql_match as $source =>$tofill) {
	$ganzo[$tofill] = $tofill.' as '.$source;
	}

$ganzosql = implode(", ", $ganzo); 

if(strstr($tabella_msql, "posts")) 
 {$ganzosql .= ", ID as idcustom";
 $dove = " WHERE (post_type LIKE  '$posts_t_cat') and (post_status LIKE 'publish')";
 }

if(isset($_POST['update']))
{$ganzosql .= ",".$_POST['where2'] ." as ".$_POST['where1'];}


$righette = $wpdb->get_results("SELECT $ganzosql FROM  $tabella_msql ".$dove,  ARRAY_A); 

foreach ($righette as $righetta ) { 

unset($custom_array_pod);


foreach ($righetta as $chiave => $campo ) 
{
if(isset($_POST['update'])) 
{
if ( $chiave == $_POST['where1']) 
{ $wherray[$chiave] = $campo;} 
else {$custom_array_pod[$chiave] = $campo;}
}
else
if( $chiave == "idcustom")  {
	$zoccole =	get_post_custom($campo);
	foreach ($customizzati as $customk => $customv) {
	$custom_array_pod[$customizzati[$customk]] = $zoccole[$customk][0];
	
	}
	}
else {$custom_array_pod[$chiave] = $campo;}
}

foreach ($texto as $textok => $textov ) {
$custom_array_pod[$textok] = $textov ;}

if(!isset($_POST['update'])) {

if (array_key_exists('permalink',$keys_pod) && !array_key_exists('permalink',$custom_array_pod))
 {$custom_array_pod['permalink'] = generateSeoTitle($custom_array_pod['name']);} else
{$custom_array_pod['permalink'] = generateSeoTitle($custom_array_pod['permalink']);}

if (array_key_exists('created',$keys_pod) && !array_key_exists('created',$custom_array_pod)) { 
$custom_array_pod['created'] = current_time('mysql', 1);
}
else if(isset($_POST['unixttcreated'])) 
{$custom_array_pod['created'] = gmdate("Y-m-d\ H:i:s", $custom_array_pod['created']);}

} 

if (array_key_exists('permalink',$custom_array_pod)) {$custom_array_pod['permalink'] = generateSeoTitle($custom_array_pod['permalink']);}
if (array_key_exists('modified',$keys_pod)&& !array_key_exists('modified',$custom_array_pod)) { 
$custom_array_pod['modified'] = current_time('mysql', 1);} 
else if(isset($_POST['unixttmodified'])) 
{$custom_array_pod['modified'] = gmdate("Y-m-d\ H:i:s", $custom_array_pod['modified']);}


if(isset($_POST['update'])){

			
$wpdb->update($tabella, $custom_array_pod, $wherray);

			}	else {
$wpdb->insert( $tabella, $custom_array_pod );	
	
	}
}


//FAST MODE
//$columns_to_fill = implode(", ", array_keys($mysql_match)); 
//$source_columns = implode(", ", $mysql_match); 
//$mysql_string =	"INSERT INTO ".$tabella." (".$columns_to_fill.") SELECT ".$source_columns." FROM ".$_POST['tabella_msql'];


			
			}
		
		
	
	?>
  
    
  <?php

//rename duplicate permalinks

$duplicati = $wpdb->get_results("SELECT permalink FROM ".$tabella." group by permalink having count(*) >= 2") ;
foreach($duplicati as $duplicato) 
{
	
	$cloni = $wpdb->get_results("SELECT * FROM  ".$tabella." WHERE permalink LIKE '".$duplicato->permalink."'"); 

	$conteggio = 0;
	echo 'similar or duplicate permalink found: <strong>'.$duplicato->permalink.'</strong><br />';
	foreach($cloni as $clone) 
	{ 
			$newperma = $clone->permalink.$conteggio;
			echo 'id: '.$clone->id.' - name: '.$clone->name.' - new-permalink: '.preg_replace('/0$/', '', $newperma).'<br />';
			
			if ($conteggio != 0)
			 {
					$wpdb->update($tabella, array('permalink'=> $newperma), array('id' => $clone->id));
				
			}
	$conteggio = $conteggio + 1;
	}
echo '<br />';
 }


?>


		<div style="background-color: #FFFFE0;border-color: #E6DB55;border-radius: 3px 3px 3px 3px;border-style: solid;border-width: 1px;margin: 5px 15px 2px; padding: 5px;text-align:center"><b> Successfully Imported ! </b></div>
		<div style="margin-top:30px;margin-left:10px">
		    <form class="add:the-list: validate" method="post" enctype="multipart/form-data">
			<input type="submit" id="goto" name="goto" value="Continue" />
		    </form>
		</div>
        
        <?php

		//display imported rows
	if	(count($data_rows_pod) > 1) {
		$righe_inserite = count($data_rows_pod);
		} else 
		{$righe_inserite = count($righette);
			}
		
		$ultimi = min($righe_inserite, 100);
		
		echo "<div style='width: 98%;overflow: auto;'><h1>Table: {$tabella}</h1><br />
		<h3>Imported rows: </h3>";
echo "<table class='widefat'><tr>";


			   foreach($keys_pod as  $v){
    echo "<td style='border: 1px solid #ddd;border-width: 0 1px 1px 0;background-color: bgcolor='#999999'><strong>".$v."</strong></td>";
}
echo "</tr>\n";
$linee = $wpdb->get_results( "SELECT * FROM ".$tabella." ORDER BY id DESC LIMIT $ultimi",  ARRAY_A);
 foreach($linee as $linea){

    echo "<tr>";
  
    foreach($keys_pod as  $v){
    echo "<td>".$linea[$v]."</td>";}
   

    echo "</tr>\n";
}
echo "</table></div>";		
		
		if ($ultimi == 100){
			echo "<h3>...... the prewiew table is limited to max display 100 inserted row.</h3>";
			}
		
		       
        ?>

	<?php 
// Code modified at version 1.1.2
	// Remove CSV file
        	$upload_dir = wp_upload_dir();
	        $csvdir  = $upload_dir['basedir']."/imported_csv/";
		$CSVfile = $_POST['filename'];
		if(file_exists($csvdir.$CSVfile)){
			chmod("$csvdir"."$CSVfile", 755);
			fileDeletePod($csvdir,$CSVfile); 
		}
	}
	
	
	
	
	
	
	else
	{
	?>
		
        
        <div class="wrap">
		
		     <div style="min-width:45%;float:left;height:500px;">
			<h2>ADVANCED CONTENT TYPE IMPORTER FOR PODS</h2>
			<form class="add:the-list: validate" method="post" enctype="multipart/form-data" onsubmit="return file_exist_pod();">

			<!-- File input -->
			<br/>
				
            <p><label for="tablelist">Select Table to fill</label>
			<select name="tabella" id="tabella" aria-required="true">
            <?php
			global $wpdb;
			$risultati = $wpdb->get_results( "SHOW TABLES", ARRAY_N);
				foreach( $risultati as $risultato ) {
				echo '<option value="' . $risultato[0] . '">' . $risultato[0]. '</option>';}			
			?>
            </select>
            </p>
            
            
             <p><label>Select the import mode (from CSV file or from an MySql table in the databese)</label>&nbsp;&nbsp;&nbsp;
			  
                <select name="importop" id="importop" onchange="showoptions();">
				 <option id="select" name="select">-- Select --</option>
                <option value="CSV_file">CSV file</option>
				<option value="MySql_Table">MySql Table</option>
			    </select>
			</p>
            
            <br /><br />
            
            <div id="csv_in" style="display:none"><p><label for="csv_import">Upload CSV file:</label><br/>
			    <input name="csv_import" id="csv_import" type="file" value="" /><br />
            
               <label>Delimiter</label>&nbsp;&nbsp;&nbsp;
			    <select name="delim" id="delim">
				<option value=";">;</option>
				<option value=",">,</option>
			    </select>
			</p></div>
            
            
            
             <div id="sql_in" style="display:none">  <p><label for="tablelist">Select the MySql Source Table</label>
			<select name="tabella_msql" id="tabella_msql" onchange="showpost_type();">
                	<?php
			global $wpdb;
			$tabelle_mysql = $wpdb->get_results( "SHOW TABLES", ARRAY_N);
				foreach( $tabelle_mysql as $tabella_mysql ) {
				echo '<option value="' . $tabella_mysql[0] . '">' . $tabella_mysql[0]. '</option>';}			
			?>
            </select>
            
            <div id="posts_t" style="display:none"> 
            	<label> Select Post Type to import </label>&nbsp;&nbsp;
			<select name='posts_t_cat' id='posts_t_cat'>
				<?php
				$post_types=get_post_types();
				      foreach($post_types as $key => $value){
					  $valuet = trim($value);
      				if ( '_' == $valuet{0} )
      				continue;
					if(($value!='featured_image') && ($value!='revision') && ($value!='nav_menu_item')){ ?>
					<option id="<?php echo($value);?>" name="<?php echo($value);?>"> <?php echo($value);?> </option>
				<?php   }
				      }
				?>
			</select>
            </div>
            </p></div>
            
            
            
			<p class="submit"><input id="Import" type="submit" class="button" name="Import" value="Import" style="display:none"/></p>
			</form>
		     </div>
		   
		</div><!-- end wrap -->
	<?php
	}
}

?>
