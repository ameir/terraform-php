<?php

require_once '../vendor/autoload.php';

use Terraform\Blocks\Resource;
use Terraform\Helpers\Aws as AwsHelpers;
use Terraform\Macros\Aws\Aws as AwsMacros;

$terraform = new \Terraform\Terraform();

$provider = new \Terraform\Blocks\Provider('aws');
$provider->region = 'us-east-1';
$terraform->provider = $provider;

foreach (['prod', 'dev', 'staging'] as $env) {
    $lc = new Resource('aws_launch_configuration', 'my_launch_configuration_' . $env);
    $lc->image_id = 'ami-eb02508e';
    $lc->instance_type = "t2.micro";
    $lc->key_name = 'my_key';
    $lc->lifecycle = ["create_before_destroy" => true];
    $terraform->{"lc_$env"} = $lc;
}

$policy = new Resource('aws_iam_role_policy', 'foo_policy');
$policy->name = "foo_policy";
$policy->role = '${aws_iam_role.foo_role.id}';
$policy->prop = 'prop';
$terraform->policy = $policy;

$sg = AwsMacros::securityGroup('my_sg', 'vpc-12345678');
$sg->description = 'We can update properties like this.';
$terraform->sg = $sg;

$terraform->save();

$aws = new AwsHelpers\Aws();
$azs = $aws->listAvailabilityZones();
print_r($azs);