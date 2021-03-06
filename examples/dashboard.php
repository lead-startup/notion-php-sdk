<?php

use Illuminate\Support\Collection;
use Notion\NotionClient;
use Notion\Records\Blocks\CollectionRowBlock;
use Notion\Records\Blocks\PageBlock;

require './_bootstrap.php';

$client = new NotionClient(getenv('MADEWITHLOVE_NOTION_TOKEN'));

/** @var PageBlock $project */
$project = $client->getBlock(getenv('URL_PROJECT_PAGE'));

/** @var Collection|CollectionRowBlock[] $sprints */
$sprints = $project->findChildByTitle('Grooming')->getRows();

/** @var CollectionRowBlock $nextSprint */
$nextSprint = $sprints->sortBy('ends')->firstWhere('status', '=', 'Future');

/** @var Collection $issuesGroomed */
$issuesGroomed = $nextSprint
    ->getChildren()
    ->first(function (Notion\Records\Blocks\BasicBlock $block) {
        return (bool) $block->getCollection();
    })
    ->getCollection()
    ->getRows();

/** @var CollectionRowBlock[] $pendingProposals */
$pendingProposals = $project
    ->findChildByTitle('Proposals')
    ->findChildByTitle('Proposals')
    ->getRows()
    ->where('status', '=', 'Review');

$sprint = $sprints->firstWhere('status', '=', 'Current');

function statistic(string $title, $value): void
{
    if (is_callable($value)) {
        ob_start();
        $value();
        $value = ob_get_clean();
    } else {
        $value = ' <h2 class="p-3 m-0 font-weight-lighter">'.$value.'</h2>';
    } ?>
    <article class="card">
        <div class="card-body text-left d-flex p-0">
            <h3 class="card-title text-uppercase text-right bg-dark text-white p-3 font-weight-lighter m-0 "
                style="width: 25%">
                <?= $title; ?>
            </h3>
            <?= $value; ?>
        </div>
    </article>
    <?php
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Project dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootswatch/4.3.1/pulse/bootstrap.min.css">
</head>
<body style="padding: 2rem">
<div class="container">
    <h1>Project Dashboard</h1>
    <section class="card-grid">
        <?php statistic('Current sprint', $sprint->title); ?>
        <?php statistic('Deadline', $sprint->ends->format('Y-m-d')); ?>
        <?php statistic('Issues groomed', $issuesGroomed->count()); ?>
        <?php statistic('Pending proposals', function () use ($pendingProposals): void {
    ?>
            <div class="list-group list-group-flush flex-fill">
                <?php foreach ($pendingProposals as $proposal) { ?>
                    <a class="list-group-item list-group-item-action" href=" <?= $proposal->getUrl(); ?>"
                       target="_blank   ">
                        <?= $proposal->title; ?>
                        by <strong><?= $proposal->author; ?></strong>
                    </a>
                <?php } ?>
            </div>
            <?php
}); ?>
    </section>
</div>
<script
    src="https://code.jquery.com/jquery-3.4.1.min.js"
    integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
    crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
