<?php

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function get_pull_data($pullNum, $accountName, $repoName, $client, $log){
    sleep(1);
    try {
        $pullRequest = $client->api('pull_request')->show($accountName, $repoName, $pullNum);

        return $pullRequest;
    } catch (Exception $e) {
        $log->warning("Pull retrieving error");
        $log->warning($e->getMessage());

        return null;
    }
}

function get_ticket_url($string, $ticketPattern){
    $matches = null;
    $returnValue = preg_match_all($ticketPattern, $string, $matches);

    return $matches[0];
}

function get_account_name($string, $accounts){
    foreach ($accounts as $accCode => $accName){
        if (strstr($string, $accCode)) return $accName;
    }

    return null;
}

function get_repo_name($string, $repos){
    foreach ($repos as $repoCode => $repoName){
        if (strstr($string, $repoCode)) return $repoName;
    }

    return null;
}


$accounts = [
    "MIR" => "MIR24",
    "volcan" => "volcanolog"
];

$repos = [
    "bc" => "backend-client",
    "bs" => "backend-server",
    "fs" => "frontend-server",
    "db" => "database"
];

$ticketPatterns = "/MIR24-[0-9]+|MIRSCR-[0-9]+|ST-[0-9]+/";

$outputFile = "tickets.txt";

$client = new \Github\Client();
$client->authenticate('github login', 'github pass', \Github\Client::AUTH_HTTP_PASSWORD);

// create a log channel
$log = new Logger('name');
$log->pushHandler(new StreamHandler('harvester.log', Logger::INFO));

$log->info("Start");

$dir = new DirectoryIterator(dirname(__FILE__)."/pull-nums");
foreach ($dir as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $fileName = $fileinfo->getFilename();
        $log->info("Parsing file $fileName");

        $accountName = get_account_name($fileName, $accounts);
        $repoName = get_repo_name($fileName, $repos);
        $log->info("Examing account $accountName, repo $repoName");

        $fileContent = file_get_contents(__DIR__."/pull-nums/".$fileName);
        $pullNums = str_getcsv($fileContent, "\n");

        foreach($pullNums as $onePullNum){
            $log->info("Trying to get data for  pull request $onePullNum");

            $pullData = get_pull_data($onePullNum, $accountName, $repoName, $client, $log);
            if($pullData) $log->debug("Pull-request data", $pullData);
            else $log->warning("Empty pull data returned");
            $log->info("Pull-request Title:".$pullData["title"]);
            $log->info("Pull-request Body:".$pullData["body"]);

            $ticketNumFromTitle = get_ticket_url($pullData["title"], $ticketPatterns);
            $log->info("Ticket num", $ticketNumFromTitle);
            $ticketNumFromBody = get_ticket_url($pullData["body"], $ticketPatterns);
            $log->info("Ticket num", $ticketNumFromBody);

            $tickets = array_merge($ticketNumFromTitle, $ticketNumFromBody);
            $log->info("Tickets found", $tickets);

            if(count($tickets)){
                $log->info("Writing to file $outputFile");
                file_put_contents($outputFile, implode("\n",$tickets)."\n" , FILE_APPEND);
            }
        }
    }
}
