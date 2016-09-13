<?php

namespace Teamgate\Transport;

interface Transport
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const DELETE = 'delete';

    public function request($url, $method, $arguments = array());
}