<?php

namespace LLMSpeak\Ollama\Enums;

enum OllamaRole: string
{
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case SYSTEM = 'system';
    case TOOL = 'tool';
}
