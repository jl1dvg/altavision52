<?php /* Smarty version 2.6.2, created on 2018-02-19 16:02:52
         compiled from C:/xampp/htdocs/altavision/templates/documents/general_list.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', 'C:/xampp/htdocs/altavision/templates/documents/general_list.html', 21, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>

<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];  ?>" type="text/css">

<?php echo '
'; ?>


<script src="DocumentTreeMenu.js" language="JavaScript" type="text/javascript"></script>
</head>
<!--<body bgcolor="<?php echo $this->_tpl_vars['STYLE']['BGCOLOR2']; ?>
">-->

<!-- ViSolve - Call expandAll function on loading of the page if global value 'expand_document' is set -->
<?php  if ($GLOBALS['expand_document_tree']) {   ?>
  <body class="body_top" onload="javascript:objTreeMenu_1.expandAll();return false;">
<?php  } else {  ?>
  <body class="body_top">
<?php  }  ?>

<div class="title"><?php echo smarty_function_xl(array('t' => 'Documents'), $this);?>
</div>
<div id="documents_list">
<table>
	<tr>
		<td height="20" valign="top"><?php echo smarty_function_xl(array('t' => 'Categories'), $this);?>
 &nbsp;
            (<a href="#" onclick="javascript:objTreeMenu_1.collapseAll();return false;"><?php echo smarty_function_xl(array('t' => 'Collapse all'), $this);?>
</a>)
		</td>
	</tr>
	<tr>
		<td valign="top"><?php echo $this->_tpl_vars['tree_html']; ?>
</td>
	</tr>
</table>
</div>
<div id="documents_actions">
		<?php if ($this->_tpl_vars['message']): ?>
			<div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo $this->_tpl_vars['message']; ?>
</i></div><br>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['messages']): ?>
            <div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo $this->_tpl_vars['messages']; ?>
</i></div><br>
		<?php endif; ?>
		<?php echo $this->_tpl_vars['activity']; ?>

</div>
</body>
</html>