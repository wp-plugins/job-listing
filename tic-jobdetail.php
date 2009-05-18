<div class="tic-jobdetail">
<h2>
    <?php echo $job->jobtitle ?><br/>
    <small><?php echo $job->companyname ?></small><br/>
    <small><?php echo $job->location ?></small><br/>
    <small><?php echo $job->country ?></small>
</h2>
<?php if ($job->salary!='') { ?>
<p>Salary: <?php echo $job->salary ?></p>
<?php } ?>
<h3>Description</h3>
<p>
    <?php echo $job->description ?>
</p>
<?php if ($job->about!='') { ?>
<h3>About Us</h3>
<p>
    <?php echo $job->about ?>
</p>
<?php } ?>
<a href="<?php echo $job->link ?>" target="_blank">More details to apply for the job</a>
</div>