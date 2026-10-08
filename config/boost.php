<?php

return [
    // Keep package rules scoped instead of expanding every task's context.
    'rules' => [
        'enabled' => true,
        'scoped_guidelines' => true,
    ],

    'agents' => [
        'claude_code' => [
            'guidelines_path' => 'AGENTS.md',
            'skills_path' => '.agents/skills',
        ],
    ],
];
