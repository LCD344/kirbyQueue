<div class="dashboard-box">
	<?php if (count($failedJobs)) { ?>
        <ul class="dashboard-items">
			<?php foreach ($failedJobs as $job) { ?>
                <li class="dashboard-item">
                    <figure class="normal-white-space">
                        <figcaption class="dashboard-item-text " style="padding: 0.5em 0 0.5em 0.5em">
                            <span style="float: right">
                                <a class="btn btn-rounded btn-small"
                                   href="<?= url("/panel/queue/retry/{$job['file']}") ?>" target>
                                    <i class="icon fa fa-refresh"></i>
                                </a>

                                <a class="btn btn-rounded btn-negative btn-small"
                                   href="<?= url("/panel/queue/remove/{$job['file']}") ?>" target>
                                    <i class="icon fa fa-remove"></i>
                                </a>
                            </span>
                            <p>
                                <strong><?= $job['job']['job']['title'] ?></strong>
                            </p>
                            <em><?= $job['job']['job']['error'] ?></em>
                        </figcaption>
                    </figure>
                </li>
			<?php } ?>
        </ul>
	<?php } else { ?>
        <div class="text">There are no failed jobs</div>
	<?php } ?>

</div>
