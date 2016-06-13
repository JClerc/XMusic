<?php

namespace Model;

/**
 * Base music provider
 *
 * Used to download song from artist/track
 */
abstract class MusicProvider extends Model {

    public abstract function match($artist, $track);

    public abstract function download(\Entity\MatchResult $matchResult);

}
