<h3>Annotations</h3>

<?php
$annotations = $this->requestAction(array('controller' => 'annotations', 'action' => 'view', $id, 'ext' => 'json'));
$ann = $annotations ? json_decode($annotations) : null;
$size = $ann ? $ann->size : "0";
?>
<p><?php echo "This object has " . $size . " annotations"; ?></p>
<h4><?php echo $this->Html->link('Annotate this object <span class="glyphicon glyphicon-new-window"></span>', ANNOSYS_BASE_URL . "/AnnoSys?recordURL=http://nabelek.sav.sk/services/record/$id/abcd2.06", array('target' => '_annosys', 'escape' => false)); ?></h4>

<?php
if ($ann && $ann->hasAnnotation) :
    foreach ($ann->annotations as $a) :
        $time_in_sec = $a->time / 1000;
        $datetime = date("d.m.Y H:i:s", $time_in_sec);
        ?>
        <a href="<?php echo ANNOSYS_BASE_URL . '/AnnoSys?repositoryURI=' . $a->repositoryURI; ?>" class="comment" title="Show details" target="_annosys">
            <div class="panel panel-default comment">
                <div class="panel-heading">
                    <span class="text-info"><?php echo $a->annotator; ?></span>
                    <span class="pull-right text-info"><?php echo $datetime; ?></span>
                </div>
                <div class="panel-body">
                    <label>Type of annotation:</label> <?php echo $a->motivation; ?>
                </div>
            </div>
        </a>
        <?php
    endforeach;
endif;
?>

<div class="row">
    <span class="col-xs-12">Powered by <?php echo $this->Html->link($this->Html->image('web/AnnoSys_logo.png', array('alt' => 'AnnoSys', 'width' => '100px')), 'https://annosys.bgbm.fu-berlin.de/', array('class' => 'thumbnail', 'target' => '_annosyshome', 'escape' => false));  ?> / <?php echo $this->Html->link('User guide', 'https://annosys.bgbm.org/sites/default/files/annosys-user-guide_v1-4_2015-06-30.pdf', array('target' => '_annosysuserguide', 'title' => 'AnnoSys user guide')); ?></span>
</div>