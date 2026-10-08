<?php

return [
    // Keep package rules scoped instead of expanding every task's context.
    'rules' => [
        'enabled' => true,
        'scoped_guidelines' => true,
    ],

    // Fixed so generated output does not depend on the machine; testing rules live in AGENTS.md and .ai/rules.
    'enforce_tests' => false,

    // Not applicable: DLF deploys with Envoy, not Laravel Cloud.
    'guidelines' => [
        'exclude' => ['deployments'],
    ],

    // Not applicable: this site does not build MCP servers.
    'skills' => [
        'exclude' => ['mcp-development'],
    ],

    // Generated guidance should name Bun, the project's script runner.
    'executable_paths' => [
        'npm' => 'bun',
    ],

    'agents' => [
        'claude_code' => [
            'guidelines_path' => 'AGENTS.md',
            'skills_path' => '.agents/skills',
        ],
    ],
];
