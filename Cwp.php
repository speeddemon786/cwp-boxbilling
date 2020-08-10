<?php

// Centos Web Panel Server Manager For BoxBilling http://www.boxbilling.com/
// This source file is subject to the license that is bundled
// with this package in the file LICENSE.txt
// Created by Grant Bamford https://www.speeddemon.co.za/
// Version 1.0 (8/8/2020)   

class Server_Manager_Cwp extends Server_Manager
{
    public function init() 
    {
        if (!extension_loaded('curl')) {
            throw new Server_Exception('cURL extension is not enabled');
        }
        
        $this->_config['version'] = '1.0';
	}

    public static function getForm()
    {
        return array(
            'label'     =>  'Centos Web Panel',
        );
    }

    public function getLoginUrl()
    {
        if ($this->_config['secure']) {
        	return 'http://'.$this->_config['host'] . ':2083';
		} else {
			return 'https://'.$this->_config['host'] . ':2083';
		}
    }

    public function getResellerLoginUrl()
    {
        return $this->getLoginUrl();
    }

    private function _makeRequest($data, $endpoint)
    {
        $this->getLog()->debug(sprintf('Centos Web Panel endpoint "%s" called with params: "%s" ', $endpoint, print_r($data,1)));
		$host = 'http';
		if ($this->_config['secure']) {
			$host .= 's';
        }
        $port = '2304';
        if ($this->_config['port']){
            $port = $this->_config['port'];
        }
        $host .= '://' . $this->_config['host'] . ':' . $port . '/v1/' . $endpoint;
    	$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        if (curl_errno ($ch)) {
            throw new Server_Exception('Error connecting to server : ' . curl_errno ($ch) . ' - ' . curl_error ($ch));
        }
        curl_close($ch);
        if (preg_match("/OK/i", $response)) {
            $result = true;
        } else {
            $json = json_decode($response);
            if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Server_Exception('The server sent back an invalid response');
            }else{
                $message = $json->msj;
                throw new Server_Exception($message);
            }
        }
        return $result;
    }

    public function testConnection()
    {
        $this->getLog()->info('Testing Connection');
        $data = array('key' => $this->_config['accesshash'], 'action' => 'list');
        $endpoint = 'account';
        $result = $this->_makeRequest($data, $endpoint);
        if (isset($result['return']) && $result['return'] == true) {
            return true;
        }
        return false;
    }

    public function synchronizeAccount(Server_Account $a)
    {
        $this->getLog()->info('Synchronizing account with server '.$a->getUsername());
        return $a;
    }

    public function createAccount(Server_Account $a)
    {
        if($a->getReseller()) {
            $this->getLog()->info('Creating reseller hosting account');
        } else {
            $this->getLog()->info('Creating shared hosting account');
            $p = $a->getPackage();
            $c = $a->getClient();
            $data = array(
                'key' => $this->_config['accesshash'],
                'action' => 'add', 
                'domain' => $a->getDomain(),
                'user' => $a->getUsername(),
                'pass' => $a->getPassword(),
                'email' => $c->getEmail(),
                'package' => $p->getCustomValue('packageid'),
                'inode' => $p->getCustomValue('inode'),
                'limit_nproc' => $p->getCustomValue('limit_nproc'),
                'limit_nofile' => $p->getCustomValue('limit_nofile'),
                'server_ips' => $this->_config['ip']
            );
            $endpoint = 'account';
            $result = $this->_makeRequest($data, $endpoint);
            if (isset($result['return']) && $result['return'] == true) {
                return true;
            }
            return false;
        }
    }

    public function suspendAccount(Server_Account $a)
    {
        if($a->getReseller()) {
            $this->getLog()->info('Suspending reseller hosting account');
        } else {
            $this->getLog()->info('Suspending shared hosting account');
            $data = array('key' => $this->_config['accesshash'], 'action' => 'susp', 'user' => $a->getUsername());
            $endpoint = 'account';
            $result = $this->_makeRequest($data, $endpoint);
            if (isset($result['return']) && $result['return'] == true) {
                return true;
            }
            return false;
        }
    }

    public function unsuspendAccount(Server_Account $a)
    {
        if($a->getReseller()) {
            $this->getLog()->info('Unsuspending reseller hosting account');
        } else {
            $this->getLog()->info('Unsuspending shared hosting account');
            $data = array('key' => $this->_config['accesshash'], 'action' => 'unsp', 'user' => $a->getUsername());
            $endpoint = 'account';
            $result = $this->_makeRequest($data, $endpoint);
            if (isset($result['return']) && $result['return'] == true) {
                return true;
            }
            return false;
        }
    }

    public function cancelAccount(Server_Account $a)
    {
        if($a->getReseller()) {
            $this->getLog()->info('Cancelling reseller hosting account');
        } else {
            $this->getLog()->info('Cancelling shared hosting account');
            $c = $a->getClient();
            $data = array('key' => $this->_config['accesshash'], 'action' => 'del', 'user' => $a->getUsername(), 'email' => $c->getEmail());
            $endpoint = 'account';
            $result = $this->_makeRequest($data, $endpoint);
            if (isset($result['return']) && $result['return'] == true) {
                return true;
            }
            return false;
        }
    }

    public function changeAccountPackage(Server_Account $a, Server_Package $p)
    {
        if($a->getReseller()) {
            $this->getLog()->info('Updating reseller hosting account');
        } else {
            $this->getLog()->info('Updating shared hosting account');
            $c = $a->getClient();
            $p = $a->getPackage();
            $data = array(
                'key' => $this->_config['accesshash'], 
                'action' => 'upd', 
                'domain' => $a->getDomain(), 
                'user' => $a->getUsername(), 
                'pass' => $a->getPassword(), 
                'email' => $c->getEmail(), 
                'package' => $p->getCustomValue('packageid'), 
                'inode' => $p->getCustomValue('inode'), 
                'limit_nproc' => $p->getCustomValue('limit_nproc'), 
                'limit_nofile' => $p->getCustomValue('limit_nofile'), 
                'server_ips' => $this->_config['ip']
            );
            $endpoint = 'account';
            $result = $this->_makeRequest($data, $endpoint);
            if (isset($result['return']) && $result['return'] == true) {
                return true;
            }
            return false;
        }
    }

    public function changeAccountUsername(Server_Account $a, $new)
    {
        throw new Server_Exception('Server manager does not support username changes');
    }

    public function changeAccountDomain(Server_Account $a, $new)
    {
        throw new Server_Exception('Server manager does not support domain changes');
    }

    public function changeAccountPassword(Server_Account $a, $new)
    {
        if($a->getReseller()) {
            $this->getLog()->info('Changing reseller hosting account password');
        } else {
            $this->getLog()->info('Changing shared hosting account password');
            $data = array('key' => $this->_config['accesshash'], 'action'=>'udp' , 'user' => $a->getUsername(), 'pass' => $new);
            $endpoint = 'changepass';
            $result = $this->_makeRequest($data, $endpoint);
            if (isset($result['return']) && $result['return'] == true) {
                return true;
            }
            return false;
        }
    }

    public function changeAccountIp(Server_Account $a, $new)
    {
        throw new Server_Exception('Server manager does not support ip changes');
    }
}
