<?php

namespace JonasWindmann\PocketEngine\game;

enum GameState
{
    case WAITING;
    case PLAYING;
    case ENDED;
}