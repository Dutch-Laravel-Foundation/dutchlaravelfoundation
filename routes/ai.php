<?php

declare(strict_types=1);

use App\Mcp\Servers\DlfServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', DlfServer::class);
