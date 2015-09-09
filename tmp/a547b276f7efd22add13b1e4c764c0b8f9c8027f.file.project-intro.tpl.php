<?php /* Smarty version Smarty-3.1.14, created on 2015-08-26 11:00:21
         compiled from "/home/windmill/public_html/arm-dev2/media/com_form2content/templates/project-intro.tpl" */ ?>
<?php /*%%SmartyHeaderCode:80291181655ddd4850a04c0-64258142%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a547b276f7efd22add13b1e4c764c0b8f9c8027f' => 
    array (
      0 => '/home/windmill/public_html/arm-dev2/media/com_form2content/templates/project-intro.tpl',
      1 => 1440600902,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '80291181655ddd4850a04c0-64258142',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'PROJECT_IMAGE' => 0,
    'JOOMLA_TITLE' => 0,
    'PROJECT_INTRO' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.14',
  'unifunc' => 'content_55ddd4851f0c13_15195515',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_55ddd4851f0c13_15195515')) {function content_55ddd4851f0c13_15195515($_smarty_tpl) {?><div class="project-intro-image">
	<img src="<?php echo $_smarty_tpl->tpl_vars['PROJECT_IMAGE']->value;?>
">
</div>
<div class="project-intro-text">
	<h2><?php echo $_smarty_tpl->tpl_vars['JOOMLA_TITLE']->value;?>
</h2>
	<p><?php echo $_smarty_tpl->tpl_vars['PROJECT_INTRO']->value;?>
</p>
</div><?php }} ?>