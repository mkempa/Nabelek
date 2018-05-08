<!DOCTYPE>
<html>

    <head>
        <?php echo $this->Html->charset(); ?>
        <title>
            <?php echo $this->fetch('title'); ?>
        </title>
        <?php
        echo $this->Html->meta('author', 'Ruven Pillay &lt;ruven@users.sourceforge.netm&gt;');
        echo $this->Html->meta('keywods', 'IIPImage HTML5 Ajax IIP Zooming Streaming High Resolution Mootools SAV Nabelek Herbarium');
        echo $this->Html->meta('description', 'IIPImage: High Resolution Remote Image Streaming Viewer');
        echo $this->Html->meta('copyright', '&copy; 2003-2011 Ruven Pillay');
        echo $this->Html->meta('viewport', 'width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;');
        echo $this->Html->meta('apple-mobile-web-app-capable', 'yes');
        echo $this->Html->meta('apple-mobile-web-app-status-bar-style', 'black-translucent');

        echo $this->Html->css('iipi/iip');
        
        echo $this->Html->script('iipi/mootools-core-1.3.2-full-nocompat');
        echo $this->Html->script('iipi/mootools-more-1.3.2.1');
        echo $this->Html->script('iipi/protocols');
        echo $this->Html->script('iipi/iipmooviewer-2.0');

        echo $this->fetch('meta');
        echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
        <meta http-equiv="X-UA-Compatible" content="IE=9" />

        <link rel="shortcut icon" href="iip/iip-favicon.png" />
        <link rel="apple-touch-icon" href="iip/iip.png" />

        <title>IIPMooViewer 2.0 :: IIPImage High Resolution HTML5 Ajax Image Streaming Viewer</title>

        <script type="text/javascript">

            // The iipsrv server path (/fcgi-bin/iipsrv.fcgi by default)
            var server = '/fcgi-bin/iipsrv.fcgi';

            // The *full* image path on the server. This path does *not* need to be in the web
            // server root directory. On Windows, use Unix style forward slash paths without
            // the "c:" prefix
            //var images = '/home/mkempa/Downloads/' + <?php echo $image; ?>;
            var images = '/scans/nabelek/ptiff/<?php echo $image; ?>';

            // Copyright or information message
            var credit = '&copy; copyright or information message';

            // Create our viewer object
            // See documentation for more details of options
            var iipmooviewer = new IIPMooViewer("viewer", {
                image: images,
                server: server,
                credit: credit,
                scale: 20.0,
                showNavWindow: true,
                showNavButtons: true,
                winResize: true,
                protocol: 'iip'
            });

        </script>

        <style type="text/css">
            body{ height: 100%; }
            div#viewer{ width: 100%; height: 100%; }
        </style>

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
        <div id="viewer"></div>

    </body>
</html>