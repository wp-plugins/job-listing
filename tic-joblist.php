<div class="tic-joblist">
    <table>
        <tr>
            <td colspan="2">
                <div class="tic-search-container">
                    <form method="get">
                        <label for="tic-search">Search </label><input type="text" name="ticq"/><input type="submit" value="Submit"/>
                    </form>
                </div>
            </td>
        </tr>
        <?php foreach ($jobs as $job) { ?>
        <tr>
            <td>
                <a href="<?php echo $job->detail ?>"><?php echo $job->jobtitle ?></a><br/>
                <?php echo $job->companyname ?><br/>
                <?php echo $job->location ?><br/>
                <?php echo $job->country ?>
            </td>
            <td><?php echo $job->summary ?></td>
        </tr>
    <?php } ?>
    </table>
</div>