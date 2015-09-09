<?php
defined('JPATH_PLATFORM') or die('Restricted acccess');

require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'form2content.php');

jimport('joomla.application.component.view');
jimport('joomla.language.helper');

class Form2ContentViewForm extends JViewLegacy
{
	protected $form;
	protected $item;
	protected $fields;
	protected $state;
	protected $jArticle;
	protected $jsScripts = array();
	protected $nullDate;
	protected $pageTitle;
	protected $contentTypeSettings;
	protected $renderCaptcha = '';
	protected $submitForm = '';
	protected $itemId;
	protected $dateFormat = '';
	protected $params;
	protected $settings;
	protected $return_page;
	private $f2cConfig;
	protected $translatedFields;
	protected $contentType;
	
	function display($tpl = null)
	{
		$app				= JFactory::getApplication();
		$model 				= $this->getModel();		
		$db					= $this->get('Dbo');
		$this->f2cConfig	= F2cFactory::getConfig();
		$this->state		= $this->get('State');
		$this->params		= $app->getParams();
		$this->nullDate		= $db->getNullDate();		
		$this->dateFormat	= $this->f2cConfig->get('date_format');
		$this->itemId		= $app->input->getInt('Itemid');	
		
		$this->PrepareSettings($model);
		
		if((int)$this->settings->get('editmode', -1) == 0 || (int)$this->settings->get('editmode', -1) == 1)
		{
			if((int)$this->settings->get('editmode') == 1)
			{
				// edit existing form or create a new one
				$formId = $model->getDefaultArticleId((int)$this->settings->get('contenttypeid'));
			}
			else
			{
				$formId = 0;
			}
			
			// Initialize the state -> For the first getState call,
			// the internal data will be overwritten
			$dummy = $model->getState($this->getName().'.id');
			$model->setState($this->getName().'.id', $formId);
			
			$ids[]	= $formId;
			$app->setUserState('com_form2content.edit.form.id', $ids);			
		}		
		
		if ((int)$this->settings->get('classic_layout', 0))
		{
			$this->setLayout('classic');
			$model->classicLayout = true;
		}

		$this->item			= $this->get('Item');		
		$this->form			= $this->get('Form');
		$this->return_page	= $this->get('ReturnPage');		
		
		$data = $app->getUserState('com_form2content.edit.form.data', array());
		
		if(!empty($data))
		{
			$this->item->fields = unserialize($data['fieldData']);
			$contentTypeId = $data['projectid'];
		}
		else 
		{
			$contentTypeId = $this->item->projectid;
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) 
		{
			throw new Exception(implode("\n", $errors));
		}

		$lang = JFactory::getLanguage();
		// load com_content language file
		$lang->load('com_content', JPATH_ADMINISTRATOR);
		
		if($this->f2cConfig->get('custom_translations', false))
		{
			// load F2C custom translations
			$lang->load('com_form2content_custom', JPATH_SITE);
		}
		
		$this->translatedFields = $model->loadFieldTranslations($contentTypeId, $lang->getTag());	  	
		
		// set the state to indicate this is a new form or an existing one
		$app->setUserState('com_form2content.edit.form.new', $this->item->id ? false : true);
		
		$this->contentType = F2cFactory::getContentType($contentTypeId);
		
		$this->contentTypeSettings = new JRegistry();
		$this->contentTypeSettings->loadArray($this->contentType->settings);
		
		$this->prepareForm($this->contentType);
		$this->addToolbar($this->contentType);

		// Set the page title
		$doc = JFactory::getDocument();
		$doc->setTitle(HtmlHelper::getPageTitle($this->params->get('page_title', '')));
						
		parent::display($tpl);		
	}	
	
	protected function addToolbar($contentType)
	{
		if($this->settings->get('editmode', -1) == -1)
		{
			// coming from Article Manager menu entry
			$this->pageTitle = $this->contentTypeSettings->get('article_caption');
		}
		else 
		{
			// coming from single Article menu entry
			$this->pageTitle = $this->params->get('show_page_heading', 1) ? $this->escape($this->params->get('page_heading')) : $this->contentTypeSettings->get('article_caption');
		}
	}
	
	private function prepareForm($contentType)
	{
		$this->jsScripts['validation']	= 'var arrValidation=new Array;';
		$this->jsScripts['fieldInit']	= '';
				
		$this->form->setFieldAttribute('id', 'label', Jtext::_('COM_FORM2CONTENT_ID'));
		$this->form->setFieldAttribute('id', 'description', '');
		
		$this->overrideFieldLabel('id', $this->contentTypeSettings->get('id_caption'));
		$this->overrideFieldLabel('title', $this->contentTypeSettings->get('title_caption'));
		$this->overrideFieldLabel('alias', $this->contentTypeSettings->get('title_alias_caption'));
		$this->overrideFieldLabel('metakey', $this->contentTypeSettings->get('metakey_caption'));
		$this->overrideFieldLabel('metadesc', $this->contentTypeSettings->get('metadesc_caption'));
		$this->overrideFieldLabel('catid', $this->contentTypeSettings->get('category_caption'));
		$this->overrideFieldLabel('created_by', $this->contentTypeSettings->get('author_caption'));
		$this->overrideFieldLabel('created_by_alias', $this->contentTypeSettings->get('author_alias_caption'));
		$this->overrideFieldLabel('state', $this->contentTypeSettings->get('state_caption'));
		$this->overrideFieldLabel('featured', $this->contentTypeSettings->get('featured_caption'));
		$this->overrideFieldLabel('access', $this->contentTypeSettings->get('access_level_caption'));
		$this->overrideFieldLabel('language', $this->contentTypeSettings->get('language_caption'));
		$this->overrideFieldLabel('intro_template', $this->contentTypeSettings->get('intro_template_caption'));
		$this->overrideFieldLabel('main_template', $this->contentTypeSettings->get('main_template_caption'));
		$this->overrideFieldLabel('created', $this->contentTypeSettings->get('created_caption'));
		$this->overrideFieldLabel('publish_up', $this->contentTypeSettings->get('publish_up_caption'));
		$this->overrideFieldLabel('publish_down', $this->contentTypeSettings->get('publish_down_caption'));
		$this->overrideFieldLabel('tags', $this->contentTypeSettings->get('tags_caption'));

	  	$translatedDateFormat 	= F2cDateTimeHelper::getTranslatedDateFormat();
		
		$validationCounter = 0;
		
		if(count($this->item->fields))
		{
			foreach($this->item->fields as $field)
			{
				if($field->frontvisible)
				{
					$this->jsScripts['validation'] 	.= $field->getClientSideValidationScript($validationCounter);
					$this->jsScripts['fieldInit']	.= $field->getClientSideInitializationScript();
				}
			}
		}
						
		// Add validation scripts for the datefields
		if($this->contentTypeSettings->get('date_created_front_end'))
		{
			$label = JText::_($this->form->getFieldAttribute('created', 'label'), true);
			$this->jsScripts['validation'] .= F2C_Validation::createDatePickerValidation('jform_created', $label, $this->dateFormat, $translatedDateFormat, false);
		}

		if($this->contentTypeSettings->get('frontend_pubsel'))
		{
			$label = JText::_($this->form->getFieldAttribute('publish_up', 'label'), true);
			$this->jsScripts['validation'] .= F2C_Validation::createDatePickerValidation('jform_publish_up', $label, $this->dateFormat, $translatedDateFormat, false);
			
			$label = JText::_($this->form->getFieldAttribute('publish_down', 'label'), true);
			$this->jsScripts['validation'] .= F2C_Validation::createDatePickerValidation('jform_publish_down', $label, $this->dateFormat, $translatedDateFormat, false);
		}
		
		// Handle the captcha
		if($this->contentTypeSettings->get('captcha_front_end'))
		{
			if(!function_exists('recaptcha_get_html'))
			{
				require_once(JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'recaptcha'.DIRECTORY_SEPARATOR.'recaptchalib.php');
			}
			
			$this->renderCaptcha .= '<tr><td colspan="2"><br/>'.recaptcha_get_html($this->f2cConfig->get('recaptcha_public_key')).'</td></tr>';
			$this->submitForm = 'F2C_CheckCaptcha(task, \''.JText::_('COM_FORM2CONTENT_ERROR_CAPTCHA_INCORRECT', true).'\','.$this->itemId.'); return false;';
		}
		else
		{
			$this->submitForm = 'Joomla.submitform(task, document.getElementById(\'adminForm\'));';
		}
	}
	
	private function overrideFieldLabel($field, $caption, $group = null)
	{
		// only override the field label when a value has been provided
		if($caption)
		{
			$this->form->setFieldAttribute($field, 'label', $caption);
		}
	}
		
	protected function renderFormTemplate()
	{
		$parser = new F2cParser();
		$varsInTemplate = array();	
		$formVars = array('F2C_ID' => 'F2C_ID', 'F2C_TITLE' => 'F2C_TITLE', 'F2C_TITLE_ALIAS' => 'F2C_TITLE_ALIAS',
						  'F2C_METADESC' => 'F2C_METADESC', 'F2C_METAKEY' => 'F2C_METAKEY',
						  'F2C_CATID' => 'F2C_CATID', 'F2C_CREATED_BY' => 'F2C_CREATED_BY',
						  'F2C_CREATED_BY_ALIAS' => 'F2C_CREATED_BY_ALIAS', 'F2C_ACCESS' => 'F2C_ACCESS',
						  'F2C_INTRO_TEMPLATE' => 'F2C_INTRO_TEMPLATE', 'F2C_MAIN_TEMPLATE' => 'F2C_MAIN_TEMPLATE',
						  'F2C_CREATED' => 'F2C_CREATED', 'F2C_PUBLISH_UP' => 'F2C_PUBLISH_UP',
						  'F2C_PUBLISH_DOWN' => 'F2C_PUBLISH_DOWN', 'F2C_STATE' => 'F2C_STATE',
						  'F2C_LANGUAGE' => 'F2C_LANGUAGE', 'F2C_FEATURED' => 'F2C_FEATURED', 'F2C_TAGS' => 'F2C_TAGS');
		
		if(count($this->item->fields))
		{
			foreach($this->item->fields as $field)
			{
				$formVars[strtoupper($field->fieldname)] = strtoupper($field->fieldname);
			}
		}

		if(!$parser->addTemplate($this->contentTypeSettings->get('form_template'), F2C_TEMPLATE_INTRO))
		{
			$this->setError($parser->getError());
			return false;				
		}

		$parser->getTemplateVars($formVars, $varsInTemplate);

		// add the buttons
		if($this->item->id == 0)
		{
			$parser->addVar('F2C_BUTTON_CANCEL', '<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton(\'form.cancel\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_CANCEL').'</button>');
		}
		else
		{
			$parser->addVar('F2C_BUTTON_CANCEL', '<button type="button" class="f2c_button f2c_cancel" onclick="javascript:Joomla.submitbutton(\'form.cancel\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_CLOSE').'</button>');
		}
		
		$parser->addVar('F2C_BUTTON_SAVE', '<button type="button" class="f2c_button f2c_save" onclick="javascript:Joomla.submitbutton(\'form.save\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_SAVE').'</button>');
		$parser->addVar('F2C_BUTTON_APPLY', '<button type="button" class="f2c_button f2c_apply" onclick="javascript:Joomla.submitbutton(\'form.apply\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_APPLY').'</button>');

		if($this->settings->get('show_save_and_new_button'))
		{
			$parser->addVar('F2C_BUTTON_SAVE_AND_NEW', '<button type="button" class="f2c_button f2c_saveandnew" onclick="javascript:Joomla.submitbutton(\'form.save2new\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_SAVE_AND_NEW').'</button>');
		}
		else 
		{
			$parser->addVar('F2C_BUTTON_SAVE_AND_NEW', '');
		}
		
		if($this->settings->get('show_save_as_copy_button'))
		{
			$parser->addVar('F2C_BUTTON_SAVE_AS_COPY', '<button type="button" class="f2c_button f2c_saveascopy" onclick="javascript:Joomla.submitbutton(\'form.save2copy\')">'.JText::_('COM_FORM2CONTENT_TOOLBAR_SAVE_AS_COPY').'</button>');
		}
		else 
		{
			$parser->addVar('F2C_BUTTON_SAVE_AS_COPY', '');
		}
		
		// Add the default form fields
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('id_front_end', 1), 'F2C_ID', 'id');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('title_front_end'), 'F2C_TITLE', 'title');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('title_alias_front_end'), 'F2C_TITLE_ALIAS', 'alias');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('metadesc_front_end'), 'F2C_METADESC', 'metadesc');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('metakey_front_end'), 'F2C_METAKEY', 'metakey');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_catsel'), 'F2C_CATID', 'catid');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('author_front_end'), 'F2C_CREATED_BY', 'created_by');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('author_alias_front_end'), 'F2C_CREATED_BY_ALIAS', 'created_by_alias');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('access_level_front_end'), 'F2C_ACCESS', 'access');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_templsel'), 'F2C_INTRO_TEMPLATE', 'intro_template');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_templsel'), 'F2C_MAIN_TEMPLATE', 'main_template');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('date_created_front_end'), 'F2C_CREATED', 'created');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_pubsel'), 'F2C_PUBLISH_UP', 'publish_up');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('frontend_pubsel'), 'F2C_PUBLISH_DOWN', 'publish_down');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('state_front_end'), 'F2C_STATE', 'state');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('language_front_end'), 'F2C_LANGUAGE', 'language');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('featured_front_end'), 'F2C_FEATURED', 'featured');
		$this->addF2cJoomlaVar($parser, $varsInTemplate, $this->contentTypeSettings->get('tags_front_end', 0), 'F2C_TAGS', 'tags');
		
		$parser->addVar('F2C_CAPTCHA', $this->renderCaptcha);

		// User defined fields
		if(count($this->item->fields))
		{
			foreach ($this->item->fields as $field) 
			{
				// skip processing of hidden fields
				$parms 		= array();																		
				$fieldname 	= strtoupper($field->fieldname);

				if($field->frontvisible)
				{
					if(array_key_exists($fieldname, $varsInTemplate))
					{
						$parser->addVar($fieldname.'_CAPTION', $field->renderLabel($this->translatedFields));
						$parser->addVar($fieldname, '<div class="f2c_field">'.$field->render($this->translatedFields, $this->contentType->settings, $parms, $this->form, $this->item->id).'</div>');
					}
					else 
					{
						throw new Exception(Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_NOT_PRESENT), $fieldname));
					}
				}
				else 
				{
					if(array_key_exists($fieldname, $varsInTemplate))
					{
						throw new Exception(Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_PRESENT), $fieldname));
					}
				}
			}
		}
		
		echo $parser->parseIntro();
	}
	
	private function addF2cJoomlaVar($parser, $varsInTemplate, $condition, $title, $field)
	{
		if($condition)
		{
			if(array_key_exists($title, $varsInTemplate))
			{
				$parser->addVar($title.'_CAPTION', $this->form->getLabel($field));
				$parser->addVar($title, $this->form->getInput($field));
			}
			else
			{
				throw new Exception(Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_NOT_PRESENT), $title));
			}
		}
		else 
		{			
			// no display in front-end
			if(array_key_exists($title, $varsInTemplate))
			{
				throw new Exception(Jtext::_('COM_FORM2CONTENT_ERROR_F2C').' : '.sprintf(JText::_(COM_FORM2CONTENT_ERROR_TEMPLATE_FIELD_PRESENT), $title));
			}
		}
	}
	
	private function PrepareSettings($model)
	{		
		$this->settings	= null;
		$app 			= JFactory::getApplication();
		$menu 			= $app->getMenu();
		
		if(is_object($menu))
		{
			if ($item = $menu->getActive())
			{
				$this->settings = $menu->getParams($item->id);
			}
		}
		
		if(is_null($this->settings))
		{
			$this->settings = new JRegistry();
			$this->settings->set('editmode', 2); // direct edit
			
		}
		
		if($this->settings->get('contenttypeid', 0) == 0)
		{
			// Retrieve the item so we can set the Content Type Id for the model
			$item = $model->getItem();
			$this->settings->set('contenttypeid', (int)$item->projectid);
		}
		
		$model->contentTypeId = (int)$this->settings->get('contenttypeid');
		
		$canDo = Form2ContentHelper::getActions($this->state->get('filter.category_id'));
		
		if($this->settings->get('editmode') != '' || !$canDo->get('core.create'))
		{
			// There's no menu-item (no parameters) or we are in Single Form mode or the user is not allowed to create new articles
			$this->settings->set('show_save_and_new_button', false);
			$this->settings->set('show_save_as_copy_button', false);
		}
	}
}
?>