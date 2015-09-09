<?php /* Smarty version Smarty-3.1.14, created on 2015-08-26 11:00:21
         compiled from "/home/windmill/public_html/arm-dev2/media/com_form2content/templates/project-detail.tpl" */ ?>
<?php /*%%SmartyHeaderCode:69485892955ddd4852a2a35-83984685%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3b211c45420af9b18374bcc6af6121dea131ae3e' => 
    array (
      0 => '/home/windmill/public_html/arm-dev2/media/com_form2content/templates/project-detail.tpl',
      1 => 1440600906,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '69485892955ddd4852a2a35-83984685',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'PROJECT_IMAGE' => 0,
    'PROJECT_DETAILS' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.14',
  'unifunc' => 'content_55ddd4852aa181_69810913',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_55ddd4852aa181_69810913')) {function content_55ddd4852aa181_69810913($_smarty_tpl) {?><p><img class="project-detail-image" src="<?php echo $_smarty_tpl->tpl_vars['PROJECT_IMAGE']->value;?>
"></p>
<?php echo $_smarty_tpl->tpl_vars['PROJECT_DETAILS']->value;?>

<?php }} ?>