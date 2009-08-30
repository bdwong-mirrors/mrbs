<?php /* Smarty version 2.6.14, created on 2009-08-28 23:47:13
         compiled from phpgacl/object_search.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'phpgacl/object_search.tpl', 47, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?> 
    <style type="text/css">
    <?php echo '
    input.search {
    	width: 99%;
    }
    select.search {
    	margin-top: 0px;
    	width: 99%;
    }
    '; ?>

    </style>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/acl_admin_js.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
  </head>
  <body onload="document.object_search.name_search_str.focus();">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/navigation.tpl", 'smarty_include_vars' => array('hidemenu' => '1')));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
    <form method="get" name="object_search" action="object_search.php">
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th colspan="2"><?php echo $this->_tpl_vars['object_type_name']; ?>
 > <?php echo $this->_tpl_vars['section_value_name']; ?>
</th>
          </tr>
          <tr>
            <td width="25%"><b>Name:</b></td>
            <td width="75%"><input type="text" class="search" name="name_search_str" value="<?php echo $this->_tpl_vars['name_search_str']; ?>
" /></td>
          </tr>
          <tr>
			<td><b>Value:</b></td>
			<td><input type="text" class="search" name="value_search_str" value="<?php echo $this->_tpl_vars['value_search_str']; ?>
" /></td>
		  </tr>
		  <tr class="controls" align="center">
		  	<td colspan="2"><input type="submit" class="button" name="action" value="Search" /> <input type="button" class="button" name="action" value="Close" onClick="window.close();" /></td>
          </tr>
        </tbody>
      </table>
<?php if (( strlen ( $this->_tpl_vars['total_rows'] ) != 0 )): ?>
	  <br />
      <table cellpadding="2" cellspacing="2" border="2" width="100%">
        <tbody>
          <tr>
            <th colspan="2"><?php echo $this->_tpl_vars['total_rows']; ?>
 Objects Found</th>
          </tr>
		<?php if (( $this->_tpl_vars['total_rows'] > 0 )): ?>
          <tr valign="middle" align="center">
            <td width="90%">
			  <select name="objects" class="search" tabindex="0" size="10" multiple>
			    <?php echo smarty_function_html_options(array('options' => $this->_tpl_vars['options_objects']), $this);?>

			  </select>
            </td>
            <td width="10%">
				<input type="button" class="select" name="select" value="&nbsp;&gt;&gt;&nbsp;" onClick="opener.select_item(opener.document.forms['<?php echo $this->_tpl_vars['src_form']; ?>
'].elements['<?php echo $this->_tpl_vars['object_type']; ?>
_section'], this.form.elements['objects'], opener.document.forms['<?php echo $this->_tpl_vars['src_form']; ?>
'].elements['selected_<?php echo $this->_tpl_vars['object_type']; ?>
[]']);">
             </td>
          </tr>
		<?php endif; ?>
        </tbody>
      </table>
<?php endif; ?>
	<input type="hidden" name="src_form" value="<?php echo $this->_tpl_vars['src_form']; ?>
">
	<input type="hidden" name="object_type" value="<?php echo $this->_tpl_vars['object_type']; ?>
">	
	<input type="hidden" name="section_value" value="<?php echo $this->_tpl_vars['section_value']; ?>
">
  </form>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phpgacl/footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>