<nav class="navbar navbar-inverse navbar-fixed-top" role="search">
    <div id="navbar-image" class="hidden-sm hidden-xs">
        <div class="container">
            <?php
            echo $this->Html->image('web/title.jpg', array('alt' => 'Iter Turcico-Persicum', 'class' => 'img-responsive'));
            ?>
        </div>
    </div>
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mainNavbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span> 
            </button>
            <?php echo $this->Html->link('<span class="glyphicon glyphicon-home"><span>', '/', array('class' => 'navbar-brand', 'escape' => false)); ?>
        </div>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="nav navbar-nav">
                <li><?php echo $this->Html->link('Search for specimens', '/records/search'); ?></li>
                <li><?php echo $this->Html->link('Map of all localities', '/pages/map'); ?></li>
                <li><?php echo $this->Html->link('František Nábělek', '/pages/biography', array('title' => 'Biography data and list of publications')); ?></li>
                <li><?php echo $this->Html->link("Nábělek's papers", '/pages/papers'); ?></li>
            </ul>
            <?php
            $query = $this->params->query;
            echo $this->Form->create('FilterQuick', array('type' => 'get', 'url' => array('controller' => 'records', 'action' => 'search', 'quick'),
                'class' => 'navbar-form navbar-right', 'inputDefaults' => array('label' => false, 'div' => false)));
            ?>
            <!--<div class="form-control-static" >Quick search</div>-->
            <div class="form-group">
                <?php
                echo $this->Form->input('type', array('class' => 'form-control', 'options' => array(
                    'collnum' => 'Collection number', 'name' => 'Name', 'authors' => 'Authors', 'barcode' => 'Barcode', 'free' => 'Any'
                ), 'value' => isset($query['type']) ? $query['type'] : 'collnum'));
                ?>
            </div>
            <div class="form-group">
                <?php
                echo $this->Form->input('search-term', array('type' => 'text', 'class' => 'form-control', 'placeholder' => 'Quick search', 'value' => isset($query['search-term']) ? $query['search-term'] : ''));
                ?>
            </div>
            <?php
            echo $this->Form->end(array('label' => 'Search', 'class' => 'btn btn-default', 'div' => false));
            ?>
        </div>
    </div>
</nav>