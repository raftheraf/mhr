<?php
class printer extends object {
	function printer($id=0) {
		$this -> db = 'common';
		$this->table=$GLOBALS['table_prefix'].'dests';
		$this->id=$id;
		$this->fields_names=array(	'id'=>ucphr('ID'),
								'name'=>ucphr('NAME'),
								'dest'=>ucphr('QUEUE'),
								'driver'=>ucphr('DRIVER'),
								'preconto'=>ucphr('Preconto'),
								'preconto1'=>ucphr('Preconto1'),
								'bill'=>ucphr('BILL'),
								'bill1'=>ucphr('Ricevuta1'),
								'invoice'=>ucphr('INVOICE'),
								'invoice1'=>ucphr('Fattura1'),
								'receipt'=>ucphr('RECEIPT'),
								'language'=>ucphr('LANGUAGE'),
								'template'=>ucphr('TEMPLATE'));
		$this -> title = ucphr('PRINTERS');
		$this->file=ROOTDIR.'/admin/admin.php';
		$this->allow_single_update = array ('preconto','preconto1','bill','bill1','invoice','invoice1','receipt');
		$this->flag_delete = true;
		$this->fields_boolean=array('preconto','preconto1','bill','bill1','invoice','invoice1','receipt');
		$this->fields_width=array('name'=>'');
		$this -> fetch_data();
	}

	function list_search ($search) {
		$query = '';
		
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				RPAD('".ucphr('PRINTERS')."',30,' ') as `table`,
				".TABLE_PRINTERS." as `table_id`
				FROM `$table`
				WHERE $table.`name` LIKE '%$search%'
				";
		
		return $query;
	}
	
	function list_query_all () {
		$table = $this->table;
		
		$query="SELECT
				$table.`id`,
				$table.`name`,
				$table.`dest`,
				$table.`driver`,
				IF($table.`preconto`='0','".ucphr('NO')."','".ucphr('YES')."') as `preconto`,
				IF($table.`preconto1`='0','".ucphr('NO')."','".ucphr('YES')."') as `preconto1`,
				IF($table.`bill`='0','".ucphr('NO')."','".ucphr('YES')."') as `bill`,
				IF($table.`bill1`='0','".ucphr('NO')."','".ucphr('YES')."') as `bill1`,
				IF($table.`invoice`='0','".ucphr('NO')."','".ucphr('YES')."') as `invoice`,
				IF($table.`invoice1`='0','".ucphr('NO')."','".ucphr('YES')."') as `invoice1`,
				IF($table.`receipt`='0','".ucphr('NO')."','".ucphr('YES')."') as `receipt`,
				$table.`language`,
				$table.`template`
				 FROM `$table`
				 WHERE `deleted`='0'
				";
		
		return $query;
	}
	
	function check_values($input_data){

		$msg="";
		if($input_data['name']=="") {
			$msg=ucfirst(phr('CHECK_NAME'));
		}

		if($input_data['template']=="") {
			$msg=ucfirst(phr('CHECK_TEMPLATE'));
		}
		
		if($msg){
			echo "<script language=\"javascript\">
				window.alert(\"".$msg."\");
				window.history.go(-1);
			</script>\n";
			return 2;
		}

	if(!$input_data['preconto'])
		$input_data['preconto']=0;
		
	if(!$input_data['preconto1'])
		$input_data['preconto1']=0;
	
	if(!$input_data['bill'])
		$input_data['bill']=0;
		
	if(!$input_data['bill1'])
		$input_data['bill1']=0;
		
	if(!$input_data['invoice'])
		$input_data['invoice']=0;
		
	if(!$input_data['invoice1'])
		$input_data['invoice1']=0;

	if(!$input_data['receipt'])
		$input_data['receipt']=0;


		return $input_data;
	}

	function form(){
		if($this->id) {
			$editing=1;
			$query="SELECT * FROM `".$this->table."` WHERE `id`='".$this->id."'";
			$res=common_query($query,__FILE__,__LINE__);
			if(!$res) return mysql_errno();
			
			$arr=mysql_fetch_array($res);
		} else {
			$editing=0;
			$arr['id']=next_free_id($_SESSION['common_db'],$this->table);
			$arr['template']='receipt.tpl';
		}
		$output .= '
	<div align="center">
	<a href="?class='.get_class($this).'">'.ucphr('BACK_TO_LIST').'.</a>
	<table>
	<tr>
	<td>
	<fieldset>
	<legend>'.ucphr('PRINTER').'</legend>

	<form action="?" name="edit_form_'.get_class($this).'" method="post">
	<input type="hidden" name="class" value="'.get_class($this).'">
	<input type="hidden" name="data[id]" value="'.$arr['id'].'">';
		if($editing){
			$output .= '
	<input type="hidden" name="command" value="update">';
		} else {
			$output .= '
	<input type="hidden" name="command" value="insert">';
		}
		$output .= '
	<table>
		<tr>
			<td>
			'.ucphr('ID').':
			</td>
			<td>
			'.$arr['id'].'
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('NAME_PRINTER').':
			</td>
			<td>
			<input type="text" name="data[name]" value="'.$arr['name'].'">
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('PRINTER_DESTINATION').':
			</td>
			<td>
			<input type="text" name="data[dest]" value="'.$arr['dest'].'">
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('PRINTER_TEMPLATE').':
			</td>
			<td>
			<select name="data[template]">';
		
		$templates=list_templates(ROOTDIR.'/templates');
		for (reset ($templates); list ($key, $value) = each ($templates); ) {
			if($arr['template']==$value) $selected=' selected';
			else $selected='';

			$output .= '
			<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
		}
		$output .= '
			</select>
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('PRINTER_DRIVER').':
			</td>
			<td>
			<select name="data[driver]">';
			
		$drivers=list_drivers(ROOTDIR.'/drivers');
		for (reset ($drivers); list ($key, $value) = each ($drivers); ) {
			if($arr['driver']==$value) $selected=' selected';
			else $selected='';

			$output .= '
			<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
		}
		$output .= '
			</select>
			</td>
		</tr>
		<tr>
			<td>
			'.ucphr('PRINTER_LANGUAGE').':
			</td>
			<td>
			<select name="data[language]">';
			
		$langs=list_db_languages();
		for (reset ($langs); list ($key, $value) = each ($langs); ) {
			if($arr['language']==$value) $selected=' selected';
			else $selected='';

			$output .= '
			<option value="'.$value.'"'.$selected.'>'.$value.'</option>';
		}
			
		$output .= '
			</select>
			</td>
		</tr>
		
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[preconto]" value="1"';
		if($arr['preconto']) $output .= ' checked';
		$output .= '>'.ucphr('<b>Stampante preconti CASSA</b> - Stampa i preconti su questa stampante').'
			</td>
		</tr>
		<tr>	
			<td colspan="2">
			<input type="checkbox" name="data[preconto1]" value="1"';
		if($arr['preconto1']) $output .= ' checked';
		$output .= '>'.ucphr('<b>Stampante preconti BAR</b> - Stampa i preconti al BAR su questa stampante').'
			</td>
			
			</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[bill]" value="1"';
		if($arr['bill']) $output .= ' checked';
		$output .= '>'.ucphr('PRINTER_BILL').'
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[bill1]" value="1"';
		if($arr['bill1']) $output .= ' checked';
		$output .= '>'.ucphr('<b>Stampante Ricevuta BAR</b> - Stampa i preconti su questa stampante').'
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[invoice]" value="1"';
		if($arr['invoice']) $output .= ' checked';
		$output .= '>'.ucphr('PRINTER_INVOICE').'
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[invoice1]" value="1"';
		if($arr['invoice1']) $output .= ' checked';
		$output .= '>'.ucphr('<b>Stampante Fattura BAR</b> - Stampa i preconti su questa stampante').'
			</td>
		</tr>
		<tr>
			<td colspan="2">
			<input type="checkbox" name="data[receipt]" value="1"';
		if($arr['receipt']) $output .= ' checked';
		$output .= '>'.ucphr('PRINTER_RECEIPT').'
			</td>
		</tr>
		<tr>
			<td colspan=2 align="center">
			<table>
			<tr>
				<td>';
		if(!$editing){
			$output .= '
				<input type="submit" value="'.ucphr('INSERT').'">
	</form>
				</td>';
	} else {
		$output .= '
				<td>
				<input type="submit" value="'.ucphr('UPDATE').'">
	</form>
				</td>
				<td>
				<form action="?" name="delete_form_'.get_class($this).'" method="post">
				<input type="hidden" name="class" value="'.get_class($this).'">
				<input type="hidden" name="command" value="delete">
				<input type="hidden" name="delete[]" value="'.$this->id.'">
				<input type="submit" value="'.ucphr('DELETE').'">
				</form>
				</td>';
	}
	$output .= '
			</tr>
			</table>
			</td>
		</tr>
	</table>


	</fieldset>
	</td>
	</tr>
	</table>
	</div>';
	
	return $output;
	}
}

?>