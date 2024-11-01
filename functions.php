<?php
	
	/**
	 * Gibt einen Timestamp formatiert als Datum/Datum+Zeit zurück
	 */
	function wphb_formatTimestamp($ts, $dateOnly = false)
	{
	
		if ($ts <= 0) return '';
	
		if ($dateOnly) return date('d.m.Y', $ts);
		else return date('d.m.Y H:i:s', $ts);
	
	} // function wphb_formatTimestamp($ts)

	/**
	 * Prüfung ob die gegebene Variable numerisch ist und > 0
	 * @param 	int
	 * @return 	boolean
	 */
	function wphb_isSizedInt($int)
	{
	
		if(is_numeric($int) && (int)$int > 0)
		{
	
			return true;
	
		}
	
		return false;
	
	}

	function wphb_isSizedArray($value)
	{
		
		if (!is_array($value)) return false;
		
		if (sizeof($value) <= 0) return false;
		
		return true;
		
	} // function wphb_isSizedArray($value)
	
	/**
	 * Prüft ob eine Varible ein String ist und die Länge > 0 ist
	 */
	function wphb_isSizedString($strValue)
	{
	
		if (!isset($strValue) || !is_string($strValue)) return false;
	
		if (strlen($strValue) <= 0) return false;
	
		return true;
	
	} // function wphb_isSizedString($strValue)
	
	/**
	 * Erweiterung der Gettext Funktion um flexible Parameter
	 * Aufruf in der Form: translate(__("Es wurden #1# Häuser gefunden.", "wphb"));
	 *
	 * Zusätzlich wird der String noch durch Htmlspecialchars gejagt
	 */
	function wphb_translate($string)
	{
	
		$arg = array();
	
		for($i = 1 ; $i < func_num_args(); $i++)
		{
	
			$arg = func_get_arg($i);
			$string = preg_replace("/#".$i."#/", $arg, $string);
	}
	
	return $string;
	
	} // function wphb_translate($string)
	
	/**
	 * Escape Funktion für die Datenbank
	 */
	function wphb_q($value)
	{
		
		if (is_array($value))
		{
			
			foreach ($value as $k => $v)
			{
				
				$value[$k] = wphb_q($v);
				
			}
			
			return $value;
			
		}
		else
		{
		
			return esc_sql($value);
			
		}
		
	} // function wphb_q($value)
	
	/**
	 * Debug Funktion, die den übergebenen Wert ausgibt wenn die Option im Backend aktiviert ist.
	 */
	function wphb_debug($value)
	{
	 
		if (is_array($value))
		{
	
			echo '<pre style="color:red;">';
			print_r($value);
			echo '</pre>';
	
		}
		else
		{
			echo '<pre style="color:red;">'.$value.'</pre>';
		}
	
	} // function wphb_debug($value)
	
	function wphb_drawForm_Input($field_name, $field_label, $field_value)
	{
	
		$field_id = $field_name;
		$field_id = preg_replace('/\[|\]/', '', $field_id);
	
		$class_div = '';
		$class_p = '';
		$class = '';
		$att = '';
	
		$strReturn = '
		<div class="wphb_form_field">
		<div class="wphb_form_left">
		<label for="'.$field_id.'">'.$field_label.':</label>
		</div>
		<div class="'.$class_div.'wphb_form_right">
		';
	
		$strType = 'text';
	
		$strReturn .= '<input id="'.$field_id.'" type="'.$strType.'" class="text '.$class.'" '.$att.' name="'.$field_name.'" value="'.htmlspecialchars($field_value).'" />';
	
		$strReturn .= '</div></div>';
	 
		return $strReturn;
	
	
	} // function wphb_drawForm_Input($field_name, $field_label, $field_value, $conf = array())

?>