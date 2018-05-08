<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            Iter Turcico-Persicum Nábělek's Herbarium - <?php echo $this->fetch('title'); ?>
        </title>
        <?php
        echo $this->Html->meta('icon');
        echo $this->Html->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        echo $this->Html->meta('description', 'Digitized herbarium of František Nábělek contains 6465 specimens from'
                . ' the area of the current Israel, Palestine, Jordan, Syria, Lebanon, Iraq, Bahrein, Iran and Turkey'
                . ' collected from March 1909 until November 1910.');
        echo $this->Html->meta('keywords', 'Iter,Turico,Persicum,Turcico-Persicum,František Nábělek,Nábělek,Nabelek,herbarium');

        //echo $this->Html->css('cake.generic');
        echo $this->Html->css('bootstrap.min');
        //echo $this->Html->css('bootstrap-theme.min');
        echo $this->Html->css('lightbox');
        echo $this->Html->css('style');

        echo $this->Html->script('jquery-1.11.3.min.js');
        echo $this->Html->script('bootstrap.min');
        echo $this->Html->script('https://maps.googleapis.com/maps/api/js?key=AIzaSyAPfXxTTVEBVoV7WmUbET8qsQxr16-v6lE');
        echo $this->Html->script('http://malsup.github.com/jquery.cycle2.js');
        echo $this->Html->script('http://malsup.github.com/jquery.cycle2.center.js');
        echo $this->Html->script('lightbox');
        echo $this->Html->script('comments');
        echo $this->Html->script('main');

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
    </head>
    <body>
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-79858799-1', 'auto');
            ga('send', 'pageview');
        </script>
        <?php echo $this->element('navbar'); ?>
        <div class="container">
            <div id="content">
                <?php echo $this->Session->flash(); ?>
                <?php echo $this->fetch('content'); ?>
            </div>
        </div>
        <div id="bugzilla" class="bg-primary">
            <div class="container">
                <span>Bug reports at <a href="http://dataflos.sav.sk:8080/bugzilla/describecomponents.cgi?product=Nabelek%20herbarium" target="_bugzilla" rel="nofollow">&Gt;our Bugzilla&Lt;</a></span>
            </div>
        </div>
        <footer id="footer">
            <div class="container text-center">
                Copyright Institute of Botany, Slovak Academy of Sciences<br />
                v1.0
            </div>
        </footer>
        <?php //echo $this->element('sql_dump'); ?>
    </body>
</html>
