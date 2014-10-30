<?php namespace Paxifi\Support\Socket;

use Ratchet\ConnectionInterface;

class SocketAuth
{
    /**
     * @var null
     */
    protected $userId = null;

    /**
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function authenticate(ConnectionInterface $connection)
    {

        try {

            if (($token = $this->getTokenFromQuery($connection)) && $this->validateToken($token)) {

                $this->attachUserToConnection($connection, $token);

                return true;
            }

            return false;

        } catch (\Exception $e) {

            return false;

        }

    }

    /**
     * Extract the token value from query after socket handshake.
     *
     * @param ConnectionInterface $connection
     * @return null
     */
    protected function getTokenFromQuery(ConnectionInterface $connection)
    {
        $query = $connection->WebSocket->request->getQuery()->urlEncode();

        return $query['token'] ?: null;
    }

    /**
     * Validate the JWT token.
     *
     * @param $token
     * @return bool
     */
    protected function validateToken($token)
    {
        try {

            $result = \App::make('League\OAuth2\Server\Storage\SessionInterface')->validateAccessToken($token);

            $this->userId = $result['owner_id'];

            return true;

        } catch (\Exception $e) {

            return false;

        }
    }

    /**
     * Store the user id and token in connection instance.
     *
     * @param ConnectionInterface $connection
     * @param $token
     */
    protected function attachUserToConnection(ConnectionInterface $connection, $token)
    {
        $connection->userId = $this->userId;
        $connection->userToken = $token;
    }

}