<?php unset($this->_scripts[JURI::root(true).'/media/jui/js/bootstrap.min.js']); ?>
<?php
/**
 * @package tpl_justified_nav
 * @version 1.0.0
 * @author Windmill Design
 * @link http://cambridgesoftware.co.uk/
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

//No Direct Access
defined('_JEXEC') or die;

//Add class to body tag
  $itemid = JRequest::getVar('Itemid');
  $menu = &JSite::getMenu();
  $active = $menu->getItem($itemid);
  $params = $menu->getParams( $active->id );
  $pageclass = $params->get( 'pageclass_sfx' );

// Adjusting content width
if ($this->countModules('sidebar'))
{
  $contentwidth = "col-md-9";
}
else
{
  $contentwidth = "col-md-12";
}
?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<!-- Joomla Head -->
	<jdoc:include type="head" />
    <link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/bootstrap.css" rel="stylesheet">
	  <link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/style.css" rel="stylesheet">
    <link href="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/css/responsive.css" rel="stylesheet">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href='http://fonts.googleapis.com/css?family=Montserrat:400,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Merriweather:400,300,700,900' rel='stylesheet' type='text/css'>
 </head>

<body class="<?php echo $pageclass ?> <?php echo $itemid ?>">
    <header class="site-header">
      <div class="container">

        <div class="row">
          <div class="logo col-sm-5">
            <a href="/"><img class="img-responsive" src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo.png"></a>
          </div>
          <div class="top-right col-sm-7 pull-right">
            <div class="site-search">
              <jdoc:include type="modules" name="search" style="none" />
            </div>
            <div class="login">
              <h5><a href="/login"><i class="fa fa-user"></i>Login</a></h5>
            </div>
          </div>
        </div>

        <nav class="navbar navbar-default">
          <div class="navbar-header">
            <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
          </div>
          <div class="navbar-collapse collapse" id="navbar">
            <jdoc:include type="modules" name="menu" style="none" />
          </div><!--/.nav-collapse -->
        </nav>
      </div><!--.container-->
    </header>

     <!-- Homepage Slider Area -->
    <?php if($this->countModules('slider')) : ?>
    <div class="slider-area">
      <div class="container">
        <div class="slider">
            <jdoc:include type="modules" name="slider" style="none" />
        </div>
        <div class="featured-project col-md-3">
            <jdoc:include type="modules" name="featured-project" style="none" />
        </div>
      </div><!--.container-->
      <div class="menu-bar">
        <div class="container">
          <jdoc:include type="modules" name="menu-bar" style="none" />
        </div>
      </div>
    </div>
    <?php endif; ?>
    <!--end of Homepage Slider Area-->

    <!--Homepage Only Content Boxes-->
    <?php if($this->countModules('upcoming-events') || $this->countModules('custom-content') || $this->countModules('bye-bye')) : ?>
    <div class="homepage-content container">
      <div class="row">
        <div class="upcoming-events col-md-4">
          <jdoc:include type="modules" name="upcoming-events" style="xhtml" />
        </div>
        <div class="custom-content col-md-4">
          <jdoc:include type="modules" name="custom-content" style="none" />
        </div>
        <div class="bye-bye col-md-4">
          <jdoc:include type="modules" name="bye-bye" style="none" />
        </div>
      </div>
    </div>
    <?php endif; ?>
    <!--End of Only Content Boxes-->

    <!--Start of Content Area-->
    <div class="page-title">
      <jdoc:include type="modules" name="interior-banners" style="none" />
      <div class="container">
        <h1><?php $mydoc =& JFactory::getDocument(); $mytitle = $mydoc->getTitle(); echo $mytitle; ?></h1>
      </div>
    </div>

    <div class="container">
      <div class="row">
        <div class="main-content <?php echo $contentwidth; ?>">
            <?php if($this->countModules('before-content')) : ?>
              <div class="before-content">
                <jdoc:include type="modules" name="before-content" style="well" />
              </div>
            <?php endif; ?>
            <jdoc:include type="message" />
            <jdoc:include type="component" />
            <?php if($this->countModules('after-content')) : ?>
              <div class="after-content">
                <jdoc:include type="modules" name="after-content" style="well" />
              </div>
            <?php endif; ?>
        </div><!--/. col-md-12 or .col-md-8 -->

        <!-- Sidebar -->
        <?php if($this->countModules('sidebar')) : ?>
            <div class="sidebar col-md-3">
                <jdoc:include type="modules" name="sidebar" style="well" />
            </div>
        <?php endif; ?>
      </div><!--./ row -->
    </div> <!-- /container -->
    <!--End of Content Area-->

    <!--Start of Features, if applied-->
    <?php if($this->countModules('feature1') || $this->countModules('feature2') || $this->countModules('feature3')) : ?>
      <div class="footer-features">
        <div class="container">
          <div class="row">
            <?php if($this->countModules('feature1')) : ?>
              <div class="feature1 col-md-4">
                <jdoc:include type="modules" name="feature1" style="none" />
              </div>
            <?php endif; ?>
            <?php if($this->countModules('feature2')) : ?>
              <div class="feature2 col-md-4">
                <jdoc:include type="modules" name="feature2" style="none" />
              </div>
            <?php endif; ?>
            <?php if($this->countModules('feature3')) : ?>
              <div class="feature 3 col-md-4">
                <jdoc:include type="modules" name="feature3" style="none" />
              </div>
            <?php endif; ?>
          </div><!--.row-->
        </div><!--.container-->
      </div><!--footer-features-->
    <?php endif; ?>
    <!--End of Features-->

    <!-- Footer Logos, if applied-->
    <?php if($this->countModules('footer-logos')) : ?>
    <div class="logos">
      <div class="container">
        <div class="row">
          <div class="col-sm-12 text-center">
            <jdoc:include type="modules" name="footer-logos" style="none" />
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <footer class="site-footer">
      <div class="container">
        <div class="row">
          <div class="footer-menu col-lg-5">
            <jdoc:include type="modules" name="footer-menu" style="none" />
          </div>
          <div class="copyright col-lg-7">
            <p>&copy; <?php echo date('Y'); ?> ARM of MN <span class="divider"> | </span> P.O. Box 211542, 2955 Eagandale Blvd, Eagan 55121 <span class="divider"> | </span> (952) 707-1250</p>
          </div>
        </div>
      </div>
    </footer>

	<script defer src="templates/<?php echo $this->template ?>/js/bootstrap.js"></script>
	<script defer src="templates/<?php echo $this->template ?>/js/jquery.js"></script>
	<script defer src="templates/<?php echo $this->template ?>/js/script.js"></script>
	<!-- end scripts-->

</body>
</html>


