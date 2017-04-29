<?php
/**
* This is a working class that I use for "secure" sessions stored in database.
* It shows how easy is to implement this with ORMizer hidding the database access layer.
*/
namespace pAppBase;

require_once('ORMizer/ORMizer.inc.php');
require_once('pTools/Encrypter.php');

use ORMizer\ORMizer;
use ORMizer\TokenGenerator;
use pTools\Encrypter;

class Session {

    private $encryption_key = '';
    private $access_time;
    private $data;
    // Properties that we do not want in the database.
    private $lifetime = 'ormizer_excluded';
    private $ormized_session = 'ormizer_excluded';


    function __construct() {
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );

        register_shutdown_function('session_write_close');

        // Generates an encryption key for the session.
        $token_gen = new TokenGenerator(128);
        $this->encryption_key = $token_gen->getToken();
        // Sets the session object ORMized.
        $this->ormized_session = ORMizer::persist($this);
    }

    public function init($session_name, $secure=false, $lifetime=1800) {
        $httponly = true;
        $session_hash = 'sha512';
        $this->lifetime = $lifetime;

        if (in_array($session_hash, hash_algos())) {
            ini_set('session.hash_function', $session_hash);
        }

        ini_set('session.hash_bits_per_character', 5);
        ini_set('session.use_only_cookies', 1);

        $cookieParams = session_get_cookie_params();
        session_set_cookie_params($lifetime, $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
        session_name($session_name);
        session_start();
        session_regenerate_id(true);
    }

    public function open() {
        // Sets type casting and creates the session table in the database.
        if(!$this->ormized_session->existsTable()) {
            $this->ormized_session
                ->setCasting('ormizer_id', 'char', 128)
                ->setCasting('encryption_key', 'char', 128)
                ->setCasting('access_time', 'datetime')
                ->setCasting('data', 'text');
            $this->ormized_session->createTable();
        }
        return true;
    }

    public function close() {
        $this->garbageColector($this->lifetime);
        return true;
    }

    public function read($id) {
        $this->ormized_session->load($id);
        $encrypter = new Encrypter($this->encryption_key);
        return $encrypter->decrypt($this->ormized_session->data);
    }

    public function write($id, $data) {
        $this->ormized_session->ormizer_id = $id;
        $encrypter = new Encrypter($this->encryption_key);
        $this->ormized_session->data = $encrypter->encrypt($data);
        $this->ormized_session->access_time = new \DateTime();
        $this->ormized_session->save();
        return true;
    }

    public function destroy($id) {
        $sessionName = session_name();
        $sessionCookie = session_get_cookie_params();
        $this->ormized_session->delete();
        setcookie(
            $sessionName,
            false,
            $sessionCookie['lifetime'],
            $sessionCookie['path'],
            $sessionCookie['domain'],
            $sessionCookie['secure']
        );
        return true;
    }

    public function gc($max) {
        $this->garbageColector($max);
    }

    public function garbageColector($lifetime) {
        $sessions = $this->ormized_session->getSavedInstances();
        if($sessions !== false) {
            $now = new \DateTime();
            $now = $now->getTimestamp(); // Transforms to Unix time.
            foreach($sessions as $session) {
                $access_time = new \DateTime($session['access_time']);
                $access_time = $access_time->getTimestamp(); // Transforms to Unix time.
                if($now > $access_time + $lifetime) {
                    $this->ormized_session->ormizer_id = $session['ormizer_id'];
                    $this->ormized_session->delete();
                }
            }
        }
        return true;
    }
}
?>
