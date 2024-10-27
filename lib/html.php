<?php
/**
 *	Contains helper functions for generating HTML code.
 *
 */

class aswp_html
{
	/**
	 * Outputs the start of the "wrap" div.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	string
	 *
	 */
	function wrap_header()
	{
		return '<div class="wrap">';
	}

	/**
	 * Outputs the end of the "wrap" div.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	string
	 *
	 */
	function wrap_footer()
	{
		return '</div>';
	}

	/**
	 * Outputs the start of the table.
	 *
	 * @since	1.0
	 * @param	string	$style
	 * @return	string
	 *
	 */
	function table_start($style = '')
	{
		return '<table class="form-table" style="'.$style.'"><tbody>';
	}

	/**
	 * Outputs the end of the table.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	string
	 *
	 */
	function table_end()
	{
		return '</tbody></table>';
	}

	/**
	 * Output the start of the form tag and adds hidden fields if there are any.
	 *
	 * @since	1.0
	 * @param	string	$method
	 * @param	string	$action
	 * @param	array	$hidden_fields
	 * @return	string
	 *
	 */
	function form_start($method = 'post', $action = '', $hidden_fields = array())
	{
		$return_html = '<form method="'.$method.'" action="'.$action.'">';

		if (count($hidden_fields) > 0)
		{
			foreach ($hidden_fields as $value)
			{
				if (isset($value['full_input']) == TRUE)
				{
					$return_html .= $value['full_input'];
				}
				else
				{
					$return_html .= '<input type="hidden" name="'.$value['name'].'" value="'.$value['value'].'">';
				}
			}
		}

		return $return_html;
	}

	/**
	 * Output the end of the form tag.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	string
	 *
	 */
	function form_end()
	{
		return '</form>';
	}

	/**
	 * Output checkbox field in table row.
	 *
	 * @since	1.0
	 * @param	string	$text
	 * @param	string	$text_after_checkbox
	 * @param	string	$field_value
	 * @param	string	$field_name
	 * @param	boolean	$checked
	 * @return	string
	 *
	 */
	function tr_row_checkbox($text = '', $text_after_checkbox = '', $field_value = '', $field_name = '', $checked = FALSE)
	{
		$field_check = $checked == TRUE ? ' checked="checked"' : '';

		return '<tr valign="top">
					<th scope="row">'.$text.'</th>
					<td> <fieldset><legend class="screen-reader-text"><span>'.$text.'</span></legend><label for="'.$field_name.'"><input name="'.$field_name.'" type="checkbox" id="'.$field_name.'" value="'.$field_value.'"'.$field_check.'> '.$text_after_checkbox.'</label></fieldset></td>
				</tr>';
	}

	/**
	 * Output input field in table row.
	 *
	 * @since	1.0
	 * @param	string	$text
	 * @param	string	$description
	 * @param	string	$field_value
	 * @param	string	$field_name
	 * @return	string
	 *
	 */
	function tr_row_input($text = '', $field_name = '', $field_value = '', $description = '')
	{
		if ($description !== '')
		{
			$description = ' <span class="description">'.$description.'</span>';
		}

		return '<tr valign="top">
					<th scope="row"><label for="'.$field_name.'">'.$text.'</label></th>
					<td> <fieldset><legend class="screen-reader-text"><span>'.$text.'</span></legend><input name="'.$field_name.'" type="text" id="'.$field_name.'" value="'.trim($field_value).'">'.$description.'</fieldset></td>
				</tr>';
	}

	/**
	 * Output the blue button.
	 *
	 * @since	1.0
	 * @param	string	$value
	 * @param	string	$field_name
	 * @param	string	$include_start_tag
	 * @param	string	$include_end_tag
	 * @return	string
	 *
	 */
	function blue_button($value = '', $field_name = 'submit', $include_start_tag = '<p class="submit">', $include_end_tag = '</p>')
	{
		return $include_start_tag.'<input type="submit" name="'.$field_name.'" id="'.$field_name.'" class="button-primary" value="'.$value.'">'.$include_end_tag;
	}

	/**
	 * Creates the top part of the list table.
	 *
	 * @since	1.0
	 * @param	array	$table_titles
	 * @return	void
	 *
	 */
	function list_table_start($table_titles = array())
	{
		if (is_array($table_titles) == FALSE || count($table_titles) == 0)
		{
			return '';
		}

		$return_str = '
<table class="wp-list-table widefat fixed posts" cellspacing="0">
	<thead>
		<tr>';

		foreach ($table_titles as $value)
		{
			if ($value['type'] == 'input')
			{
				$return_str .= '<th scope="col" id="'.$value['field_id'].'" class="manage-column column-cb check-column"><input type="checkbox"></th>';
			}

			if ($value['type'] == 'text')
			{
				$return_str .= '<th scope="col" id="'.$value['field_id'].'" class="manage-column column-title">'.$value['title'].'</th>';
			}
		}

		$return_str .= '
		</tr>
	</thead>
	<tfoot>
		<tr>';

		foreach ($table_titles as $value)
		{
			if ($value['type'] == 'input')
			{
				$return_str .= '<th scope="col" id="'.$value['field_id'].'" class="manage-column column-cb check-column"><input type="checkbox"></th>';
			}

			if ($value['type'] == 'text')
			{
				$return_str .= '<th scope="col" id="'.$value['field_id'].'" class="manage-column column-title">'.$value['title'].'</th>';
			}
		}

		$return_str .= '
		</tr>
	</tfoot>
	<tbody id="the-list">';

		return $return_str;
	}

	/**
	 * Closes the tags for the list table.
	 *
	 * @since	1.0
	 * @param	array	$table_titles
	 * @return	void
	 *
	 */
	function list_table_end()
	{
		return '</tbody></table>';
	}

	/**
	 * Start of the post box.
	 *
	 * @since	1.0
	 * @param	string	$post_box_id
	 * @param	string	$post_box_title
	 * @param	string	$custom_style
	 * @param	boolean	$closed
	 * @return	string
	 *
	 */
	function insert_post_box_start($post_box_id = '', $post_box_title = '', $custom_style = '', $closed = FALSE)
	{
		$custom_style = $custom_style !== '' ? ' style="'.$custom_style.'"' : '';

		$closed = $closed == TRUE ? ' style="display: none;"' : '';

		return '
<div id="poststuff" class="metabox-holder has-right-sidebar">
	<div id="post-body-content"'.$custom_style.'>
		<div id="normal-sortables" class="meta-box-sortables ui-sortable">
			<div id="'.$post_box_id.'" class="postbox ">
				<div class="handlediv" id="toggle_'.$post_box_id.'" title="'.__('Click to toggle', ASWP_UNIQUE_NAME).'"><br /></div>
				<h3 class="hndle"><span>'.$post_box_title.'</span></h3>
				<div class="inside" id="inside_toggle_'.$post_box_id.'"'.$closed.'>';
	}

	/**
	 * End of the post box.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	string
	 *
	 */
	function insert_post_box_end()
	{
		return '
				</div>
			</div>
		</div>
	</div>
</div>';
	}

	/**
	 * Outputs the text field for the meta box.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	string	$field_name
	 * @param	string	$field_value
	 * @return	string
	 *
	 */
	function meta_box_text_field($field_desc = '', $field_name = '', $field_value = '')
	{
		return '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input"><input type="text" name="'.$field_name.'" id="'.$field_name.'" value="'.$field_value.'" /></div>
		</div>';
	}

	/**
	 * Outputs the select field for the meta box.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	string	$field_name
	 * @param	array	$field_values
	 * @return	string
	 *
	 */
	function meta_box_select_field($field_desc = '', $field_name = '', $field_value = array())
	{
		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">
				<select name="'.$field_name.'" id="'.$field_name.'">';

		foreach ($field_value as $value)
		{
			$selected = '';
			if ($value['selected'] == 'yes')
			{
				$selected = ' selected="selected"';
			}

			$return_str .= '<option value="'.$value['value'].'"'.$selected.'>'.$value['text'].'</option>';
		}

		$return_str .= '</select>
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Outputs the checkbox field for the meta box.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	array	$checkbox_values
	 * @return	string
	 *
	 */
	function meta_box_checkbox_field($field_desc = '', $checkbox_values = array())
	{
		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">';

		foreach ($checkbox_values as $value)
		{
			$checked = '';
			if ($value['checked'] == 'yes')
			{
				$checked = ' checked="checked"';
			}

			$return_str .= '<span id="'.$value['name'].'_span"><input type="checkbox" name="'.$value['name'].'" id="'.$value['name'].'" value="'.$value['value'].'"'.$checked.'/> <span>'.$value['text'].'</span><br /></span>';
		}

		$return_str .= '
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Outputs the color pickers.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	array	$color_boxes
	 * @return	string
	 *
	 */
	function meta_box_color_pickers($field_desc = '', $color_boxes = array())
	{
		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">';

		foreach ($color_boxes as $value)
		{
			$return_str .= '<div class="aswp_cp_cont">
				<div class="aswp_cp_text">'.$value['text'].'</div>
				<div class="aswp_cp_picker" id="aswp_color_picker_'.$value['picker_id'].'"><div style="background-color: #'.$value['input_value'].';"></div></div>
				<div class="aswp_cp_input">#<input type="text" style="width: 55px;" name="'.$value['input_name'].'" id="'.$value['input_name'].'" value="'.$value['input_value'].'"/></div>
			</div>';
		}

		$return_str .= '</div>
		</div>';

		return $return_str;
	}

	/**
	 * Output the live ad preview.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	array	$colors
	 * @return	string
	 *
	 */
	function meta_box_ad_preview($field_desc = '', $colors = array())
	{
		return '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">
				<div id="aswp_ad_live_preview" style="border: 1px solid #'.$colors['aswp_border_color'].'; background-color: #'.$colors['aswp_background_color'].';">
					<div id="aswp_ad_lp_title" style="color: #'.$colors['aswp_title_color'].';"><span>'.__('Ad Title', ASWP_UNIQUE_NAME).'</span></div>
					<div id="aswp_ad_lp_text" style="color: #'.$colors['aswp_text_color'].';"><span>'.__('Ad text will be shown here...', ASWP_UNIQUE_NAME).'</span></div>
					<div id="aswp_ad_lp_url" style="color: #'.$colors['aswp_url_color'].';"><span>'.__('www.ad-link.com', ASWP_UNIQUE_NAME).'</span></div>
				</div>
			</div>
		</div>
		';
	}

	/**
	 * Outputs the select field.
	 *
	 * @since	1.0
	 * @param	string	$title
	 * @param	string	$field_name
	 * @param	array	$select_values
	 * @return	string
	 *
	 */
	function tr_select_field($title = '', $field_name = '', $select_values = array())
	{
		$return_str = '
<tr valign="top">
	<th scope="row"><label for="blogdescription">'.$title.'</label></th>
	<td>
		<select name="'.$field_name.'" id="'.$field_name.'">';

		foreach ($select_values as $value)
		{
			$selected = '';
			if ($value['selected'] == 'yes')
			{
				$selected = ' selected="selected"';
			}

			$return_str .= '<option value="'.$value['value'].'"'.$selected.'>'.$value['text'].'</option>';
		}

		$return_str .= '
		</select>
	</td>
</tr>';

		return $return_str;
	}

	/**
	 * Output the ad placement image with checkboxes.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	array	$radio_buttons
	 * @param	string	$or_text
	 * @param	array	$placements
	 * @param	array	$input_box
	 * @param	array	$paragraph_positions
	 * @return	string
	 *
	 */
	function meta_box_placements($field_desc = '', $radio_buttons = array(), $or_text = '', $placements = array(), $input_box = array(), $paragraph_positions = array())
	{
		$radio_btn_1 = $radio_buttons[0]['checked'] == 'yes' ? ' checked="checked"' : '';
		$radio_btn_2 = $radio_buttons[1]['checked'] == 'yes' ? ' checked="checked"' : '';

		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">
				<div class="aswp_placements_radio"><input type="radio" name="'.$radio_buttons[0]['name'].'" id="'.$radio_buttons[0]['name'].'" value="1"'.$radio_btn_1.'/></div>
				<div id="aswp_image_placements">';

		foreach ($placements as $value)
		{
			$checked = '';
			if ($value['checked'] == 'yes')
			{
				$checked = ' checked="checked"';
			}

			$return_str .= '<input type="checkbox" name="'.$value['name'].'" id="'.$value['name'].'" style="position: absolute; '.$value['margins_style'].'" value="'.$value['value'].'"'.$checked.'/>';
		}

		$paragraph_pos_left = $paragraph_positions['checked_left'] == 'yes' ? ' checked="checked"' : '';
		$paragraph_pos_center = $paragraph_positions['checked_center'] == 'yes' ? ' checked="checked"' : '';
		$paragraph_pos_right = $paragraph_positions['checked_right'] == 'yes' ? ' checked="checked"' : '';

		$return_str .= '
				</div>
				<div class="aswp_placements_radio"><strong style="margin-right: 10px; font-size: 14px;">'.$or_text.'</strong> <input type="radio" name="'.$radio_buttons[1]['name'].'" id="'.$radio_buttons[1]['name'].'" value="2"'.$radio_btn_2.'/></div>
				<div class="aswp_placements_input">
					<strong>'.$input_box['pre_text'].'</strong><br /><input type="text" size="2" value="'.$input_box['value'].'" name="'.$input_box['name'].'" id="'.$input_box['name'].'"/> <strong>'.$input_box['after_text'].'</strong>
					<br />
					<div id="aswp_image_placements_paragraph">
						<input style="position: absolute; '.$paragraph_positions['style_left'].'" type="checkbox" name="'.$paragraph_positions['name_left'].'" id="'.$paragraph_positions['name_left'].'" value="1"'.$paragraph_pos_left.'/>
						<input style="position: absolute; '.$paragraph_positions['style_center'].'" type="checkbox" name="'.$paragraph_positions['name_center'].'" id="'.$paragraph_positions['name_center'].'" value="1"'.$paragraph_pos_center.'/>
						<input style="position: absolute; '.$paragraph_positions['style_right'].'" type="checkbox" name="'.$paragraph_positions['name_right'].'" id="'.$paragraph_positions['name_right'].'" value="1"'.$paragraph_pos_right.'/>
					</div>
				</div>
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Output the ad placement image with checkboxes.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	string	$field_name
	 * @param	string	$field_value
	 * @param	string	$field_style
	 * @param	string	$text_behind_field
	 * @return	string
	 *
	 */
	function meta_box_input_field($field_desc = '', $field_name = '', $field_value = '', $field_style = '', $text_behind_field = '')
	{
		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">
				<input type="text" style="'.$field_style.'" name="'.$field_name.'" id="'.$field_name.'" value="'.$field_value.'"/>'.$text_behind_field.'
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Output the 4 inputs for margins.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	array	$fields
	 * @return	string
	 *
	 */
	function meta_box_margins_field($field_desc = '', $fields = array())
	{
		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">
				<div id="aswp_metabox_margins_input">';

		foreach ($fields as $value)
		{
			if ($value['name'] == 'aswp_margin_top')
			{
				$return_str .= '<div style="position: absolute; width: 150px; left: 75px;" id="'.$value['name'].'">'.$value['field_title'].' <input type="text" style="width: 40px;" value="'.$value['value'].'" name="'.$value['name'].'" id="'.$value['name'].'"/>'.__('px', ASWP_UNIQUE_NAME).'</div>';
			}
			else if ($value['name'] == 'aswp_margin_right')
			{
				$return_str .= '<div style="position: absolute; width: 150px; right: 0; top: 40px;" id="'.$value['name'].'">'.$value['field_title'].' <input type="text" style="width: 40px;" value="'.$value['value'].'" name="'.$value['name'].'" id="'.$value['name'].'"/>'.__('px', ASWP_UNIQUE_NAME).'</div>';
			}
			else if ($value['name'] == 'aswp_margin_bottom')
			{
				$return_str .= '<div style="position: absolute; width: 150px; left: 57px; top: 80px;" id="'.$value['name'].'">'.$value['field_title'].' <input type="text" style="width: 40px;" value="'.$value['value'].'" name="'.$value['name'].'" id="'.$value['name'].'"/>'.__('px', ASWP_UNIQUE_NAME).'</div>';
			}
			else if ($value['name'] == 'aswp_margin_left')
			{
				$return_str .= '<div style="position: absolute; width: 150px; left: 0; top: 40px;" id="'.$value['name'].'">'.$value['field_title'].' <input type="text" style="width: 40px;" value="'.$value['value'].'" name="'.$value['name'].'" id="'.$value['name'].'"/>'.__('px', ASWP_UNIQUE_NAME).'</div>';
			}
		}

		$return_str .= '
				</div>
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Display the updated/error notice.
	 *
	 * @since	1.0
	 * @param	string	$notice_type
	 * @param	string	$notice_text
	 * @return	string
	 *
	 */
	function admin_notice($notice_type = 'updated', $notice_text = '')
	{
		return '<div id="message" class="'.$notice_type.'"><p>'.$notice_text.'</p></div>';
	}

	/**
	 * Output the top bar above the list table.
	 *
	 * @since	1.0
	 * @param	void
	 * @return	string
	 *
	 */
	function list_table_option()
	{
		return '
		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1" selected="selected">'.__('Bulk Actions', ASWP_UNIQUE_NAME).'</option>
					<option value="delete">'.__('Delete', ASWP_UNIQUE_NAME).'</option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="'.__('Apply', ASWP_UNIQUE_NAME).'">
			</div>
			<br class="clear">
		</div>';
	}

	/**
	 * Used inside meta box to show simple text and nothing more really.
	 *
	 * @since	1.0
	 * @param	array	$content
	 * @return	string
	 *
	 */
	function meta_box_insert_content($content = array())
	{
		$content_str = '';

		foreach ($content as $value)
		{
			$content_str .= '<p>'.$value.'</p>';
		}

		return '<div class="aswp_metabox_cont_full">
			<div class="aswp_metabox_full_width">'.$content_str.'</div>
		</div>';
	}

	/**
	 * Output fields for the privacy policy page.
	 *
	 * @since	1.0
	 * @param	string	$field_desc
	 * @param	string	$checkbox_name
	 * @param	string	$checkbox
	 * @param	string	$checkbox_text_after
	 * @param	string	$description
	 * @param	string	$field_name_input
	 * @param	string	$field_value_input
	 * @param	string	$field_style_input
	 * @param	string	$field_name_textarea
	 * @param	string	$field_value_textarea
	 * @param	string	$field_style_textarea
	 * @return	string
	 *
	 */
	function meta_box_input_pp_field($field_desc = '', $checkbox_name = '', $checkbox = '', $checkbox_text_after = '', $description = '', $field_name_input = '', $field_value_input = '', $field_style_input = '', $field_name_textarea = '', $field_value_textarea = '', $field_style_textarea = '')
	{
		$checkbox_checked = '';
		if ($checkbox !== '')
		{
			$checkbox_checked = ' checked="checked"';
		}

		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc_pp">
				'.$field_desc.'
				<div style="margin: 5px 0;"><input type="checkbox" name="'.$checkbox_name.'" id="'.$checkbox_name.'" value="1"'.$checkbox_checked.'/>'.$checkbox_text_after.'</div>
				<span class="description">'.$description.'</span>
			</div>
			<div class="aswp_metabox_field_input_pp">
				<input type="text" style="'.$field_style_input.'" name="'.$field_name_input.'" id="'.$field_name_input.'" value="'.$field_value_input.'"/>
				<br />
				<textarea style="'.$field_style_textarea.'" name="'.$field_name_textarea.'" cols="120" rows="6" id="'.$field_name_textarea.'">'.stripslashes($field_value_textarea).'</textarea>
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Output the top banner.
	 *
	 * @since	1.1
	 * @param	void
	 * @return	string
	 *
	 */
	function show_banner()
	{
		return '<a href="http://www.adsensewpplugin.com/adsense-training"><img src="'.ASWP_PLUGIN_BASE_URL.'/images/banner.jpg" alt=""/></a>';
	}

	/**
	 * Output the live ad preview.
	 *
	 * @since	1.1
	 * @param	string	$field_desc
	 * @param	array	$colors
	 * @return	string
	 *
	 */
	function meta_box_tla_preview($field_desc = '', $colors = array())
	{
		return '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc">'.$field_desc.'</div>
			<div class="aswp_metabox_field_input">
				<div id="aswp_tla_live_preview" style="background-color: #'.$colors['aswp_background_color'].';">
					<div id="aswp_tla_lp_google" style="background-color: #'.$colors['aswp_border_color'].';"><span>'.__('Ads by Google', ASWP_UNIQUE_NAME).'</span></div>
					<div id="aswp_tla_lp_title" style="color: #'.$colors['aswp_title_color'].';"><span>'.__('Ad Title', ASWP_UNIQUE_NAME).'</span></div>
					<div id="aswp_tla_lp_title" style="color: #'.$colors['aswp_title_color'].';"><span>'.__('Ad Title', ASWP_UNIQUE_NAME).'</span></div>
					<div id="aswp_tla_lp_title" style="color: #'.$colors['aswp_title_color'].';"><span>'.__('Ad Title', ASWP_UNIQUE_NAME).'</span></div>
					<div id="aswp_tla_lp_title" style="color: #'.$colors['aswp_title_color'].';"><span>'.__('Ad Title', ASWP_UNIQUE_NAME).'</span></div>
				</div>
			</div>
		</div>
		';
	}

	/**
	 * Output textarea field in table row.
	 *
	 * @since	1.1
	 * @param	string	$text
	 * @param	string	$description
	 * @param	string	$field_value
	 * @param	string	$field_name
	 * @return	string
	 *
	 */
	function tr_row_textarea($text = '', $field_name = '', $field_value = '', $description = '')
	{
		if ($description !== '')
		{
			$description = ' <span class="description">'.$description.'</span>';
		}

		return '<tr valign="top">
					<th scope="row"><label for="'.$field_name.'">'.$text.'</label></th>
					<td> <fieldset><legend class="screen-reader-text"><span>'.$text.'</span></legend><textarea name="'.$field_name.'" rows="10" cols="50" id="'.$field_name.'" class="large-text code">'.trim(stripslashes($field_value)).'</textarea>'.$description.'</fieldset></td>
				</tr>';
	}

	/**
	 * Output textarea field for meta box.
	 *
	 * @since	1.1
	 * @param	string	$field_desc
	 * @param	string	$description
	 * @param	string	$field_name_textarea
	 * @param	string	$field_value_textarea
	 * @param	string	$field_style_textarea
	 * @return	string
	 *
	 */
	function meta_box_textarea_field($field_desc = '', $description = '', $field_name_textarea = '', $field_value_textarea = '', $field_style_textarea = '')
	{
		$return_str = '<div class="aswp_metabox_cont">
			<div class="aswp_metabox_field_desc_pp">
				'.$field_desc.'
				<span class="description">'.$description.'</span>
			</div>
			<div class="aswp_metabox_field_input_pp">
				<textarea style="'.$field_style_textarea.'" name="'.$field_name_textarea.'" class="large-text code" cols="50" rows="10" id="'.$field_name_textarea.'">'.stripslashes($field_value_textarea).'</textarea>
			</div>
		</div>';

		return $return_str;
	}

	/**
	 * Output the secondary button.
	 *
	 * @since	1.1
	 * @param	string	$value
	 * @param	string	$field_name
	 * @param	string	$include_start_tag
	 * @param	string	$include_end_tag
	 * @return	string
	 *
	 */
	function secondary_button($value = '', $field_name = 'submit', $include_start_tag = '<p class="submit">', $include_end_tag = '</p>')
	{
		return $include_start_tag.'<input type="submit" name="'.$field_name.'" id="'.$field_name.'" class="button-secondary" value="'.$value.'">'.$include_end_tag;
	}
}
?>