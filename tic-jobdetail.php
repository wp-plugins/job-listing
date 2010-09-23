<div class="tic-jobdetail">
<h2>
    <?php echo $job->jobtitle; ?><br/>
    <small><?php echo $job->companyname; ?></small><br/>
    <small><?php echo $job->location; ?></small><br/>
    <small><?php echo $job->country; ?></small>
</h2>
<?php if ( !empty( $job->salary ) ) { ?>
<p>Salary: <?php echo $job->salary; ?></p>
<?php } ?>
<h3>Description</h3>
<p><?php echo $job->description; ?></p>
<?php if ( !empty( $job->apply ) ) { ?>
<h3>How to apply</h3>
<p><?php echo $job->apply; ?></p>
<?php } ?>
<?php if ( !empty( $job->about ) ) { ?>
<h3>About Us</h3>
<p><?php echo $job->about; ?></p>
<?php } ?>
<?php if ( !empty( $job->name ) || !empty( $job->email ) || !empty( $job->url ) ) { ?>
<h3>Contact details</h3>
<?php if ( !empty( $job->name ) ) { ?><p>Name: <?php echo $job->name; ?></p><?php } ?>
<?php if ( !empty( $job->email ) ) { ?><p>Email: <a href="mailto:<?php echo $job->email; ?>"><?php echo $job->email; ?></a></p><?php } ?>
<?php if ( !empty( $job->url ) ) { ?><p>Website: <a href="<?php echo $job->url; ?>"><?php echo $job->url; ?></a></p><?php } ?>
<?php } ?>
</div>
<div>&nbsp;</div>
<div><a href="<?php echo $job->postlink; ?>">Post your own job on The Ideal Candidate</a></div>
