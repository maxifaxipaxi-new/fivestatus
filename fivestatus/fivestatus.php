<?php
/**
 * Plugin Name: FiveStatus - FiveM Server Status by MFPSCRIPTS.com
 * Description: Show your FiveM Online Status & Players: [fivem_status ip="127.0.0.1" port="30120" lang="de"]
 * Version: 1.0
 * Author: MFPSCRIPTS.com
 */

function fivem_server_status($ip, $port) {
    $info_url = "http://$ip:$port/info.json";
    $players_url = "http://$ip:$port/players.json";

    $response = wp_remote_get($info_url);

    if (is_wp_error($response)) {
        return array('status' => 'Offline', 'players' => 0);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['vars'])) {
        $players_response = wp_remote_get($players_url);
        if (!is_wp_error($players_response)) {
            $players_body = wp_remote_retrieve_body($players_response);
            $players_data = json_decode($players_body, true);

            $player_count = count($players_data);
            return array('status' => 'Online', 'players' => $player_count);
        }
        return array('status' => 'Online', 'players' => 0);
    } else {
        return array('status' => 'Offline', 'players' => 0);
    }
}

function fivem_status_css() {
    echo '<style>
        .fivem-status {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .fivem-status-indicator {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-bottom: 10px;
            animation: blink 1s infinite;
        }
        .fivem-status-online {
            background-color: green;
        }
        .fivem-status-offline {
            background-color: red;
        }
        @keyframes blink {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>';
}
add_action('wp_head', 'fivem_status_css');

function fivem_status_shortcode($atts) {
    $atts = shortcode_atts(array(
        'ip' => '127.0.0.1',
        'port' => '30120',
        'lang' => 'en'
    ), $atts);

    $status_info = fivem_server_status($atts['ip'], $atts['port']);
    $status = $status_info['status'];
    $players = $status_info['players'];
    $indicator_class = $status === 'Online' ? 'fivem-status-online' : 'fivem-status-offline';

    $texts = array(
        'en' => array(
            'server_status' => 'Server Status',
            'players_online' => 'Online Players',
        ),
        'de' => array(
            'server_status' => 'Server Status',
            'players_online' => 'Online Spieler',
        )
    );

    $lang = array_key_exists($atts['lang'], $texts) ? $atts['lang'] : 'en';
    $server_status_text = $texts[$lang]['server_status'];
    $players_online_text = $texts[$lang]['players_online'];

    if ($status === 'Online') {
        $players_info = "<div>{$players_online_text}: <strong>{$players}</strong></div>";
    } else {
        $players_info = "";
    }

    return "<div class='fivem-status'>
                {$server_status_text}: <strong>{$status}</strong>
                <div class='fivem-status-indicator {$indicator_class}'></div>
                {$players_info}
            </div>";
}
add_shortcode('fivem_status', 'fivem_status_shortcode');