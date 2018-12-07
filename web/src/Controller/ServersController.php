<?php

namespace SourceBans\Controller;

use SourceBans\Entity\Server;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use xPaw\SourceQuery\Exception\TimeoutException;
use xPaw\SourceQuery\SourceQuery;

class ServersController
{
    public function info(Server $server): Response
    {
        try {
            $sourceQuery = new SourceQuery();
            $sourceQuery->Connect($server->getHost(), $server->getPort());
            $serverInfo = $sourceQuery->GetInfo();
        } catch (TimeoutException $e) {
            throw new HttpException(
                Response::HTTP_REQUEST_TIMEOUT,
                sprintf('Could not connect to %s.', $server),
                $e
            );
        }

        return new JsonResponse([
            'hostname' => preg_replace('/[\x00-\x1F]/', '', $serverInfo['HostName']), // Strip UTF-8 characters
            'map' => basename($serverInfo['Map']), // Strip Steam Workshop folder
            'maxplayers' => $serverInfo['MaxPlayers'],
            'numplayers' => $serverInfo['Players'],
        ]);
    }
}
