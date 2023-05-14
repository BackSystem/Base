<?php

namespace BackSystem\Base\Test;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Finder\Finder;

class SchemaUpdateListener extends KernelTestCase implements BeforeFirstTestHook
{
    public function executeBeforeFirstTest(): void
    {
        $text = "SET FOREIGN_KEY_CHECKS=0;\r\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSTART TRANSACTION;\r\nSET time_zone = \"+00:00\";\r\n\r\n";

        $finder = new Finder();
        $files = $finder->in('tests/data/')->directories()->files();

        foreach ($files as $file) {
            $text .= 'USE `'.$file->getRelativePath()."_test`;\r\n";

            $text .= $file->getContents()."\r\n";
        }

        $text .= "\r\nSET FOREIGN_KEY_CHECKS=1;\r\nCOMMIT;";

        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = self::getContainer()->get(ManagerRegistry::class);

        /** @var \PDO $pdo */
        $pdo = $managerRegistry->getConnection();
        $pdo->exec($text);

        echo 'Schema updated'.PHP_EOL;

        $this->tearDown();
    }
}
