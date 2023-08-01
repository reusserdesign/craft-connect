<?php
/**
 * Connect plugin for Craft CMS 3.x
 *
 * Allows you to connect to external databases and perform db queries
 *
 * @link      https://nystudio107.com/
 * @copyright Copyright (c) 2018 nystudio107
 */

namespace nystudio107\connect\variables;

use Craft;
use nystudio107\connect\Connect;
use nystudio107\connect\db\Query;
use nystudio107\connect\models\Settings;
use yii\db\Connection;
use yii\db\Exception;

/**
 * @author    nystudio107
 * @package   Connect
 * @since     1.0.0
 */
class ConnectVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Open a new database connection
     *
     * @param string $configName
     *
     * @return ?Connection
     */
    public function open(string $configName): ?Connection
    {
        $connection = null;
        $config = $this->getDbConfig($configName);
        if (!empty($config)) {
            // Create a connection to the db
            $connection = new Connection($config);
            try {
                $connection->open();
            } catch (Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return $connection;
    }

    /**
     * Returns a new generic query.
     *
     * @param ?Connection $connection
     *
     * @return Query
     */
    public function query(?Connection $connection = null): Query
    {
        return new Query([
            'db' => $connection,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * Return a database config with the key $configName from the Settings
     *
     * @param string $configName
     *
     * @return array
     */
    protected function getDbConfig(string $configName): array
    {
        $config = [];
        /* @var Settings $settings */
        $settings = Connect::$plugin->getSettings();
        if ($settings !== null && !empty($settings->connections[$configName])) {
            $dbConnection = $settings->connections[$configName];
            $config = [
                'dsn' => "{$dbConnection['driver']}:"
                    . "host={$dbConnection['server']};"
                    . "dbname={$dbConnection['database']};"
                    . "port={$dbConnection['port']};",
                'username' => $dbConnection['user'],
                'password' => $dbConnection['password'],
                'tablePrefix' => $dbConnection['tablePrefix'],
            ];

            if ($dbConnection['driver'] == "sqlsrv") {
                $config['dsn'] = "{$dbConnection['driver']}:"
                    . "Server={$dbConnection['server']}"
                    . ",{$dbConnection['port']};"
                    . "Database={$dbConnection['database']};";

                if (isset($dbConnection['trust_server_certificate']) && $dbConnection['trust_server_certificate']) {
                    $config['dsn'] .= "TrustServerCertificate=true;";
                }
            }
        }

        return $config;
    }
}
