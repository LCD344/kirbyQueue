<?php

namespace lcd344\KirbyQueue\Panel;

use Kirby\Panel\Controllers\Base;
use Error;
use lcd344\KirbyQueue\Queue;

class Controller extends Base
{
    public static function retry($id)
    {
		try {
            Queue::retry($id);

            panel()->notify(':)');
        } catch (Error $e) {
            panel()->alert($e->getMessage());
        }

        panel()->redirect();
    }

    public static function remove($id)
    {
        try {
            Queue::remove($id);

            panel()->notify(':)');
        } catch (Error $e) {
            panel()->alert($e->getMessage());
        }

        panel()->redirect();
    }
}

