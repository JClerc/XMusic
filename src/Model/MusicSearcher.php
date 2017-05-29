<?php

namespace Model;

/**
 * Base music searcher
 *
 * Used to search and retrieve data from music
 */
abstract class MusicSearcher extends Model {

    public abstract function search($mode, $query, $page);

    public abstract function getAlbum($id);

    public abstract function getArtist($id);

}
