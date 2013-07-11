<?php

$inputFile  = $argv[1];
$percentage = min(100, max(0, (int)$argv[2]));

if (!file_exists($inputFile)) {
  fprintf(STDERR, 'Invalid input file provided' . PHP_EOL);
  exit(1);
}

if (!$percentage) {
  fprintf(
    STDERR,
    'An integer checked percentage must be given as second parameter'
    . PHP_EOL
  );
  exit(1);
}

$xml = new SimpleXMLElement(file_get_contents($inputFile));
/* @var $metrics SimpleXMLElement[] */
$metrics = $xml->xpath('//metrics');

$totalElements   = 0;
$checkedElements = 0;

foreach ($metrics as $metric) {
  $totalElements   += (int)$metric['elements'];
  $checkedElements += (int)$metric['coveredelements'];
}

$coverage = round(($checkedElements / $totalElements) * 100);

if ($coverage < $percentage) {
  printf(
    'Code coverage is %f%%, which is below the accepted %f%%' . PHP_EOL,
    $coverage, $percentage
  );
  exit(1);
}

printf('Code coverage is %f%% - OK!' . PHP_EOL, $coverage);
