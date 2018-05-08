<?php
$h_revision = !empty($udaj['SkupRevRev']);
$countRevs = count($udaj['SkupRevRev']);

$stable_url = 'http://ibot.sav.sk/herbarium/object/' . $udaj['HerbarPolozky']['cislo_ck_full'];
$download_url = 'http://dataflos.sav.sk:8080/scans/nabelek/jpeg/' . $udaj['HerbarPolozky']['cislo_ck_full'];
?>

<div id="details" class="row">
    <div id="images" class="col-md-4">
        <div class="row"  style="min-height: 450px;">
            <div class="col-sm-12">
                <?php
                $thumb = 'thumbs/' . $udaj['HerbarPolozky']['cislo_ck_full'] . '_thumb.jpg';
                echo $this->Html->image($thumb, array('id' => 'thumbnail', 'class' => 'img-responsive img-thumbnail center-block', 'alt' => $udaj['HerbarPolozky']['cislo_ck_full']));
                ?>
            </div>
        </div>
        <div class="row" id="img-buttons">
            <div class="col-md-2 col-md-offset-1">
                <?php echo $this->Html->link('View', array('controller' => 'images', 'action' => 'view', $udaj['HerbarPolozky']['cislo_ck_full']), array('id' => 'view', 'class' => 'btn btn-primary', 'target' => '_view')); ?>
            </div>
            <div class="col-md-2">
                <?php echo $this->Html->link('Download (jpeg)', $download_url . '.jpg', array('id' => 'download', 'class' => 'btn btn-default', 'target' => '_download')); ?>
            </div>
        </div>
        <div id="previews">
            <?php echo $this->Format->gallery($udaj['Images']); ?>
        </div>
        <div id="map-container">
        </div>
        <div id="map-message" class="text-danger"></div>
        <?php
        echo $this->Form->hidden('latitude', array('id' => 'view-record-latitude', 'value' => $udaj['Lokality']['latitude']));
        echo $this->Form->hidden('longitude', array('id' => 'view-record-longitude', 'value' => $udaj['Lokality']['longitude']));
        ?>
    </div>
    <div class="col-md-8">
        <h2 id="name">
            <?php
            if ($h_revision) {
                echo $this->Format->taxonName($udaj['SkupRevRev'][$countRevs - 1]['ListOfSpecies']['meno'], $udaj['SkupRevRev'][$countRevs - 1]['ListOfSpecies']['autori']);
            } else {
                echo $this->Format->taxonName($udaj['SkupRevOrig']['ListOfSpecies']['meno'], $udaj['SkupRevOrig']['ListOfSpecies']['autori']);
            }
            ?>
        </h2>
        <div id="basic">
            <ul class="list-group">
                <li class="list-group-item"><?php echo strtoupper(($h_revision ? $udaj['SkupRevRev'][$countRevs - 1]['Family']['meno'] : $udaj['SkupRevOrig']['Family']['meno'])); ?><span class="label label-primary pull-right">Family</span></li>
                <li class="list-group-item"><strong><?php echo $udaj['HerbarPolozky']['cislo_ck_full']; ?></strong><span class="label label-primary pull-right">Barcode</span></li>
                <li class="list-group-item bg-warning"><?php echo $this->Html->link($stable_url, $stable_url, array('data-toggle' => 'tooltip', 'title' => 'Stable identifier. Please, use this URL to cite this specimen')); ?><span class="pull-right"><a href="" data-toggle="tooltip" title="Stable identifier. Please, use this URL to cite this specimen">What is this?</a> <span class="label label-primary">Object URL</span></span></li>
                <li class="list-group-item"><?php echo join(', ', Set::classicExtract($udaj['Collectors'], '{n}.std_meno')); ?><span class="label label-primary pull-right">Collectors</span></li>
                <li class="list-group-item"><?php echo $udaj['HerbarPolozky']['cislo_zberu']; ?><span class="label label-primary pull-right">Collection N°</span></li>
                <li class="list-group-item"><?php echo $udaj['Herbar']['skratka_herb']; ?><span class="label label-primary pull-right">Herbarium</span></li>
            </ul>
        </div>
        <h3>Locality</h3>
        <div id="locality">
            <div class="panel panel-default">
                <div class="panel-heading">
                    World Geographical Scheme for Recording Plant Distributions (TDWG)
                </div>
                <ul class="list-group panel-body">
                    <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Worlds']['Brumit4']['meno']); ?><span class="label label-primary pull-right">Level 4</span></li>
                    <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Worlds']['Brumit3']['meno']); ?><span class="label label-primary pull-right">Level 3</span></li>
                    <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Worlds']['Brumit2']['meno']); ?><span class="label label-primary pull-right">Level 2</span></li>
                    <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Worlds']['Brumit1']['meno']); ?><span class="label label-primary pull-right">Level 1</span></li>
                </ul>
            </div>
            <ul class="list-group">
                <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Lokality']['opis_lokality']); ?><span class="label label-primary pull-right">Description</span></li>
                <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Lokality']['poznamka_lok']); ?><span class="label label-primary pull-right">Note</span></li>
                <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Lokality']['alt_od']); ?> m<span class="label label-primary pull-right">Altitude from</span></li>
                <li class="list-group-item"><?php echo $this->Format->chkEmpty($udaj['Lokality']['alt_do']); ?> m<span class="label label-primary pull-right">Altitude to</span></li>
                <li class="list-group-item"><?php echo $this->Format->chkEmpty($this->Format->date($udaj['Udaj']['datum_zberu'])); ?><span class="label label-primary pull-right">Date of collection</span></li>
                <li class="list-group-item"><?php echo $this->Format->chkEmpty($this->Format->coordinates($udaj['Lokality']['latitude'], $udaj['Lokality']['longitude'])); ?><span class="label label-primary pull-right">Coordinates</span></li>
            </ul>
        </div>

        <h3>Revision history</h3>
        <div>
            <table class="table table-bordered table-condensed table-responsive table-striped">
                <tr>
                    <th>Identification</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>By</th>
                </tr>
                <tr>
                    <td class="bg-info">ORIGINAL</td>
                    <td><?php echo $this->Format->taxonName($udaj['SkupRevOrig']['ListOfSpecies']['meno'], $udaj['SkupRevOrig']['ListOfSpecies']['autori']); ?></td>
                    <td><?php echo $this->Format->date($udaj['SkupRevOrig']['datum']); ?></td>
                    <td><?php echo join(', ', Set::classicExtract($udaj['SkupRevOrig']['DeterminedBy'], '{n}.std_meno')); ?></td>
                </tr>
                <?php
                for ($i = 0; $i < count($udaj['SkupRevRev']); $i++) :
                    $rev = $udaj['SkupRevRev'][$i];
                    ?>
                    <tr>
                        <?php if ($i != count($udaj['SkupRevRev']) - 1): ?>
                            <td>REVISION</td>
                        <?php else: ?>
                            <td class="bg-success">NEWEST</td>
                        <?php endif; ?>
                        <td><?php echo $this->Format->taxonName($rev['ListOfSpecies']['meno'], $rev['ListOfSpecies']['autori']) ?></td>
                        <td><?php echo $this->Format->date($rev['datum']); ?></td>
                        <td><?php echo join(', ', Set::classicExtract($rev['DeterminedBy'], '{n}.std_meno')); ?></td>
                    </tr>
                    <?php
                endfor;
                ?>
            </table>
        </div>
    </div>
</div>

<div id="comments">
    <?php 
    /*
    * $flatComments = $this->Format->flatComments($comments, array(), 0);
    * echo $this->element('comments', array('comments' => $flatComments, 'id_udaj' => $udaj['Udaj']['id'])); 
    */
    echo $this->element('annotations', array('id' => $udaj['HerbarPolozky']['cislo_ck_full']));
    ?>
</div>
<?php
