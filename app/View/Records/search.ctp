<?php
$is_udajs = isset($udajs) && !empty($udajs);

?>

<div class="collapse <?php echo!$is_udajs ? 'in' : ''; ?>" id="collapse-filter">
    <div class="panel panel-default">
        <div class="panel-body">
            <?php echo $this->element('filter', array('countries' => $countries)); ?>
        </div>
    </div>
</div>
<div class="text-center">
    <button id="collapse-filter-btn" class="btn btn-default" data-toggle="collapse" data-parent="#accordion" href="#collapse-filter" aria-expanded="false" aria-controls="collapse-filter">
        <?php if ($is_udajs): ?>
            <span class="glyphicon glyphicon-chevron-down"></span> Show advanced search fields
        <?php else: ?>
            <span class="glyphicon glyphicon-chevron-up"></span> Hide advanced search fields            
        <?php endif; ?>
    </button>
</div>
<hr />
<?php
if (!$is_udajs) :
    ?>
    <div class="text-primary">Found 0 results</div>
    <?php
endif;

if ($is_udajs):
    ?>
    <!--<div class="text-primary">Found <?php //echo count($udajs);   ?> results</div>-->
    <div class="text-primary"><?php
        echo $this->Paginator->counter(
                'Page {:page} of {:pages}, showing {:current} records out of
     {:count} total, starting on record {:start}, ending on {:end}'
        );
        ?></div>
    <div class="row text-center">
        <ul class="pagination">
            <?php echo $this->Paginator->prev('< Prev', array('tag' => 'li', 'class' => false), null, array('disabledTag' => 'a', 'class' => 'disabled')); ?>
            <?php echo $this->Paginator->numbers(array('first' => 1, 'last' => 1, 'modulus' => 6, 'tag' => 'li', 'separator' => false, 'ellipsis' => '<li class="readonly"><a>...</a></li>', 'currentTag' => 'a', 'currentClass' => 'active')); ?>
            <?php echo $this->Paginator->next('Next >', array('tag' => 'li', 'class' => false), null, array('disabledTag' => 'a', 'class' => 'disabled')); ?>
        </ul>
    </div>
    <?php
    foreach ($udajs as $udaj):
        $nameRev = $this->Format->taxonName($udaj['ListOfSpeciesRev']['meno'], $udaj['ListOfSpeciesRev']['autori']);
        $nameOrig = $this->Format->taxonName($udaj['ListOfSpeciesOriginal']['meno'], $udaj['ListOfSpeciesOriginal']['autori']);
        ?>

        <div class="row search-result">
            <div class="col-md-2">
                <?php
                $thumb = 'thumbs/' . $udaj['HerbarPolozky']['cislo_ck_full'] . '_thumb.jpg';
                echo $this->Html->link($this->Html->image($thumb, array('class' => 'img-responsive', 'alt' => $nameRev)), array('controller' => 'records', 'action' => 'view', $udaj['HerbarPolozky']['cislo_ck_full']), array('class' => 'thumbnail', 'escape' => false));
                ?>
            </div>
            <div class="col-md-10">
                <h3>
                    <?php echo $this->Html->link($nameRev, '/records/view/' . $udaj['HerbarPolozky']['cislo_ck_full'], array('escape' => false)); ?><br />
                    <small><span class="smaller">Original identification: </span><?php echo $nameOrig; ?></small>
                </h3>
                <div><span class="smaller">Barcode: </span><span class="label label-success"><?php echo $udaj['HerbarPolozky']['cislo_ck_full']; ?></span></div>
                <div class="text-primary"><span class="smaller">Collection number: </span><?php echo $udaj['HerbarPolozky']['cislo_zberu']; ?></div>
                <div><span class="smaller">Collector: </span><?php echo join(', ', Set::classicExtract($udaj['Collectors'], '{n}.std_meno')); ?></div>
                <div><span class="smaller">Current country: </span><?php echo $udaj['B4']['meno']; ?></div>
                <div><span class="smaller">Original description of locality: </span><?php echo $udaj['Lokality']['opis_lokality']; ?></div>
            </div>
        </div>

        <?php endforeach; ?>
    <div class="row text-center">
        <ul class="pagination">
            <?php echo $this->Paginator->prev('< Prev', array('tag' => 'li', 'class' => false), null, array('disabledTag' => 'a', 'class' => 'disabled')); ?>
            <?php echo $this->Paginator->numbers(array('first' => 1, 'last' => 1, 'modulus' => 6, 'tag' => 'li', 'separator' => false, 'ellipsis' => '<li class="readonly"><a>...</a></li>', 'currentTag' => 'a', 'currentClass' => 'active')); ?>
            <?php echo $this->Paginator->next('Next >', array('tag' => 'li', 'class' => false), null, array('disabledTag' => 'a', 'class' => 'disabled')); ?>
        </ul>
    </div>
    <?php
endif;

//new dBug($udajs);
//echo $this->element('sql_dump');
