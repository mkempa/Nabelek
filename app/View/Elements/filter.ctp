<?php

$countriesOpts = array();

if (isset($countries) && !empty($countries)) {
    $countriesOpts = $countries;
    $countriesOpts[999] = 'Palestine';
    asort($countriesOpts);
}

$query = $this->params->query;
echo $this->Form->create('FilterAdv', array('type' => 'get', 'url' => array('controller' => 'records', 'action' => 'search', 'advanced'),
    'role' => 'form', 'inputDefaults' => array('label' => false, 'div' => false)));
?>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <?php echo $this->Form->label('genus', 'Genus:', array('class' => 'control-label')); ?>
            <?php echo $this->Form->input('genus', array('type' => 'text', 'class' => 'form-control', 'value' => isset($query['genus']) ? $query['genus'] : '')); ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->label('species', 'Species:', array('class' => 'control-label')); ?>
            <?php echo $this->Form->input('species', array('type' => 'text', 'class' => 'form-control', 'value' => isset($query['species']) ? $query['species'] : '')); ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->label('fullname', 'Full name:', array('class' => 'control-label')); ?>
            <?php echo $this->Form->input('fullname', array('type' => 'text', 'class' => 'form-control', 'value' => isset($query['fullname']) ? $query['fullname'] : '')); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <?php echo $this->Form->label('locality', 'Locality:', array('class' => 'control-label')); ?>
            <?php echo $this->Form->input('locality', array('type' => 'text', 'class' => 'form-control', 'value' => isset($query['locality']) ? $query['locality'] : '')); ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->label('collnum', 'Collection number: <a href="" data-toggle="tooltip" title="Collection number as referred to in the Iter Turcico-Persicum">?</a>', array('class' => 'control-label')); ?>
            <?php echo $this->Form->input('collnum', array('type' => 'text', 'class' => 'form-control', 'value' => isset($query['collnum']) ? $query['collnum'] : '')); ?>
        </div>
        <div class="form-group">
            <?php echo $this->Form->label('country', 'Country: <a href="" data-toggle="tooltip" title="">?</a>', array('class' => 'control-label')); ?>
            <?php echo $this->Form->input('country', array('class' => 'form-control', 'empty' => array(0 => ''), 'options' => $countriesOpts, 'selected' => isset($query['country']) ? $query['country'] : 0)); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="row">
            <?php echo $this->Form->label('latDegrees', 'Latitude:', array('class' => 'control-label col-xs-12')); ?>
        </div>
        <div id="lat" class="form-group row lat-lon">
            <div class="col-md-3">
                <div class="input-group">
                    <?php echo $this->Form->input('latDegrees', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'max' => '180', 'value' => isset($query['latDegrees']) ? $query['latDegrees'] : '')); ?>
                    <span class="input-group-addon" id="lat-degrees-addon">°</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <?php echo $this->Form->input('latMinutes', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'max' => '59', 'value' => isset($query['latMinutes']) ? $query['latMinutes'] : '')); ?>
                    <span class="input-group-addon" id="lat-minutes-addon">'</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <?php echo $this->Form->input('latSeconds', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'max' => '59.999', 'value' => isset($query['latSeconds']) ? $query['latSeconds'] : '')); ?>
                    <span class="input-group-addon" id="lat-seconds-addon">''</span>
                </div>
            </div>
            <div class="col-md-3">
                <?php echo $this->Form->input('latOrientation', array('options' => array('N' => 'N', 'S' => 'S'), 'class' => 'form-control', 'value' => isset($query['latOrientation']) ? $query['latOrientation'] : '')); ?>
            </div>
        </div>

        <div class="row">
            <?php echo $this->Form->label('lonDegrees', 'Latitude:', array('class' => 'control-label col-xs-12')); ?>
        </div>
        <div id="lon" class="form-group row lat-lon">
            <div class="col-md-3">
                <div class="input-group">
                    <?php echo $this->Form->input('lonDegrees', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'max' => '180', 'value' => isset($query['lonDegrees']) ? $query['lonDegrees'] : '')); ?>
                    <span class="input-group-addon" id="lon-degrees-addon">°</span>
                </div>
            </div>
            <?php //echo $this->Form->label('latDegrees', '°', array('class' => 'control-label col-md-1')); ?>
            <div class="col-md-3">
                <div class="input-group">
                    <?php echo $this->Form->input('lonMinutes', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'max' => '59', 'value' => isset($query['lonMinutes']) ? $query['lonMinutes'] : '')); ?>
                    <span class="input-group-addon" id="lon-minutes-addon">'</span>
                </div>
            </div>
            <?php //echo $this->Form->label('latMinutes', "'", array('class' => 'control-label col-md-1')); ?>
            <div class="col-md-3">
                <div class="input-group">
                    <?php echo $this->Form->input('lonSeconds', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'max' => '59.999', 'value' => isset($query['lonSeconds']) ? $query['lonSeconds'] : '')); ?>
                    <span class="input-group-addon" id="lon-seconds-addon">''</span>
                </div>
            </div>
            <?php //echo $this->Form->label('latSeconds', "''", array('class' => 'control-label col-md-1')); ?>
            <div class="col-md-3">
                <?php echo $this->Form->input('lonOrientation', array('options' => array('E' => 'E', 'W' => 'W'), 'class' => 'form-control', 'value' => isset($query['lonOrientation']) ? $query['lonOrientation'] : '')); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $this->Form->label('range', 'Range in degrees:'); ?>
            <div class="input-group">
                <span class="input-group-addon">&plusmn;</span>
                <?php echo $this->Form->input('range', array('type' => 'number', 'class' => 'form-control', 'min' => '0', 'step' => '0.001', 'value' => isset($query['range']) ? $query['range'] : '')); ?>
                <span class="input-group-addon">°</span>
            </div>
        </div>
    </div>
</div>
<div class="text-center">
    <?php
    echo $this->Form->end(array('label' => 'Find', 'class' => 'btn btn-default'));
    ?>
</div>
