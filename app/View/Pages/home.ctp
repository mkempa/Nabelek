<div class="row">
    <div id="intro-left" class="col-md-9">
        <div id="cycle-container" class="panel panel-default">
            <div class="panel-body">
                <div class="custom-pager"></div>
                <div id="cycle" class="hidden-xs hidden-sm cycle-slideshow" 
                     data-cycle-pager=".custom-pager"
                     data-cycle-caption="#caption" 
                     data-cycle-caption-template="{{title}}"
                     data-cycle-center-horz="true"
                     data-cycle-center-vert="true"
                     data-cycle-slides="> a">
                         <?php echo $this->Html->link($this->Html->image('thumbs/SAV0000630_thumb.jpg', array('alt' => '<i>Galium subvelutinum</i> (DC.) K. Koch var. <i>leiophyllum</i> (Boiss. &amp; Hohen.) Bornm.')), array('controller' => 'records', 'action' => 'view', 'SAV0000630'), array('title' => '<i>Galium subvelutinum</i> (DC.) K. Koch var. <i>leiophyllum</i> (Boiss. &amp; Hohen.) Bornm.', 'escape' => false)); ?>
                         <?php echo $this->Html->link($this->Html->image('thumbs/SAV0001894_thumb.jpg', array('alt' => '<i>Aeluropus litoralis</i> (Gouan) Parl.')), array('controller' => 'records', 'action' => 'view', 'SAV0001894'), array('title' => '<i>Aeluropus litoralis</i> (Gouan) Parl.', 'escape' => false)); ?>
                         <?php echo $this->Html->link($this->Html->image('thumbs/SAV0002867_thumb.jpg', array('alt' => '<i>Ranunculus lomatocarpus</i> Fisch. &amp; C.A. Mey.')), array('controller' => 'records', 'action' => 'view', 'SAV0002867'), array('title' => '<i>Ranunculus lomatocarpus</i> Fisch. &amp; C.A. Mey.', 'escape' => false)); ?>
                         <?php echo $this->Html->link($this->Html->image('thumbs/SAV0003731_thumb.jpg', array('alt' => '<i>Atractylis comosa</i> (Spreng.) Sieber ex Cass.')), array('controller' => 'records', 'action' => 'view', 'SAV0003731'), array('title' => '<i>Atractylis comosa</i> (Spreng.) Sieber ex Cass.', 'escape' => false)); ?>
                         <?php echo $this->Html->link($this->Html->image('thumbs/SAV0005552_thumb.jpg', array('alt' => '<i>Hedysarum varium</i> Willd.')), array('controller' => 'records', 'action' => 'view', 'SAV0005552'), array('title' => '<i>Hedysarum varium</i> Willd.', 'escape' => false)); ?>
                </div>
                <div id="caption" class="text-center"></div>
            </div>
        </div>
        <div id="intro-text">
            <p>
                Czech botanist František Nábělek (1884-1965) studied botany under 
                Professor Richard von Wettstein at Vienna University, Austria. Shortly after finishing his studies 
                he visited SW Asia, where he collected plants from March 1909 until November 1910. 
                During this time he visited the area of the current Israel, Palestine, Jordan, 
                Syria, Lebanon, Iraq, Bahrein, Iran and Turkey. Along with Joseph Bornmüller and 
                Heinrich Handel-Mazzetti, Nábělek was one of the most important contributors towards 
                the knowledge of the flora of this area after the publication of Boissier's Flora 
                Orientalis. Results of his studies were published in five parts of his work Iter 
                Turcico-Persicum (Nábělek, 1923-1929), where he described four new genera, 78 species, 
                69 varieties and 38 formas. His extensive herbarium from this area contains 6775 
                specimens (altogether 4171 collection numbers).
            </p>
        </div>
    </div>
    <div id="intro-right" class="col-md-3">
        <?php
        echo $this->Html->image('web/Nabelek.jpg', array('alt' => 'František Nábělek', 'class' => 'img-responsive'));
        ?>
        <p>
            František Nábělek (1884-1965)
        </p>
    </div>
</div>