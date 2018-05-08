
<h4>
    Annotations<br />
    <small>Annotations will be reviewed and posted after approval</small>
</h4>
<div id="comments-body">
    <?php
    foreach ($comments as $comm) :
        $nested = $comm['UdajComment']['nested'] > 4 ? 4 : $comm['UdajComment']['nested']; //for level 4 and above use margin 4
        ?>
        <div class="panel panel-default comment <?php echo $nested > 0 ? "marg-$nested" : ''; ?>" >
            <?php echo $this->Form->hidden('commentId', array('value' => $comm['UdajComment']['id'], 'class' => 'comment-id')); ?>
            <div class="panel-heading">
                <span class="text-info"><?php echo $comm['UdajComment']['author'] . ' - ' . $comm['UdajComment']['institution']; ?></span>
                <span class="pull-right text-info"><?php echo preg_replace('/\.[0-9]*/', '', $comm['UdajComment']['date_posted']); ?></span>
            </div>
            <div class="panel-body">
                <?php echo $comm['UdajComment']['annotation']; ?>
            </div>
            <div class="panel-footer">
                <button class="btn btn-default btn-xs reply">Reply</button>
                <button class="btn btn-warning btn-xs close-reply">Close</button>
            </div>
        </div>
        <?php
    endforeach;
    ?>
</div>  

<button id="add-new-comment" class="btn btn-default">Add new annotation...</button>
<div id="reply-fields" class="panel panel-default">
    <div class="panel-body">
        <?php
        echo $this->Form->create('UdajComment', array('id' => 'comments-form', 'method' => 'post', 'url' => array('controller' => 'comments', 'action' => 'add')));
        echo $this->Form->hidden('parent_id', array('value' => 'null', 'id' => 'parent-id'));
        echo $this->Form->hidden('id_udaj', array('value' => $id_udaj));
        ?>
        <div class="form-group">
            <?php echo $this->Form->input('author', array('label' => 'Name', 'class' => 'form-control')); ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->input('institution', array('label' => 'Institution', 'class' => 'form-control')); ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->input('annotation', array('type' => 'textarea', 'rows' => '5', 'cols' => '60', 'label' => 'Annotation', 'class' => 'form-control')); ?>
        </div>
        <?php echo $this->Form->end(array('label' => 'Submit', 'class' => 'btn btn-primary', 'div' => false)); ?>
    </div>
</div>


<?php
/*
if (isset($success)) :
    $message = $success ? 'Annotation has been successfully added.' : 'There was an error when submitting the annotation';
    echo $this->element('modal-message', array('message' => $message));
    ?>
    <script type="text/css">

        $(function() {
            $("#modal-message").modal('show');
        });

    </script>
<?php endif;*/