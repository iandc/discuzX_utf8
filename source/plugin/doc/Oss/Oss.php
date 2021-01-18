<?php

//把SDK文件夹 aliyun-oss-php-sdk-master复制到libraries下，修改为OSS
//删掉不用的文件，只保留autoload.php 和 src 其他都删除
//参考samples/Common.php,新建oss.php 文件，
//删除autoload引用,和dz冲突
require_once '.\source\plugin\doc\Oss\autoload.php';
require_once '.\source\plugin\doc\Oss\src\OSS\OssClient.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Core\OssUtil.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Core\MimeTypes.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Http\RequestCore.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Http\ResponseCore.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Result\Result.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Result\PutSetDeleteResult.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Result\ListObjectsResult.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Model\ObjectInfo.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Model\ObjectListInfo.php';
require_once '.\source\plugin\doc\Oss\src\OSS\Result\ExistResult.php';

use OSS\Core\OssException;
use OSS\OssClient;

class Oss
{

    private $endpoint = '';
    private $accessKeyId = '';
    private $accessKeySecret = ' ';
    private $bucket = '';
    private $ossClient;

    public function __construct()
    {
        global $_G;
        $doc = $_G['cache']['plugin']['doc'];

        $this->accessKeyId = $doc['doc_oss_keyid'];
        $this->accessKeySecret = $doc['doc_oss_keysecret'];
        $this->bucket = $doc['doc_oss_bucket'];
        $this->endpoint =$doc['doc_oss_endpoint'];

        try {
            $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint, substr($this->endpoint, -12) !== 'aliyuncs.com');
        } catch (OssException $e) {
            writelog('getOssClient:' . $e->getMessage());
        }
    }

    public function createBucket()
    {
        if ($this->ossClient) {
            try {
                $this->ossClient->createBucket($this->bucket, OssClient::OSS_ACL_TYPE_PRIVATE);
            } catch (OssException $e) {
                $message = $e->getMessage();
                if (\OSS\Core\OssUtil::startsWith($message, 'http status: 403')) {
                    writelog('oss:createBucket:Please Check your AccessKeyId and AccessKeySecret');
                } elseif (strpos($message, 'BucketAlreadyExists') !== false) {
                    writelog('oss:createBucket:Bucket already exists. Please check whether the bucket belongs to you, or it was visited with correct endpoint. ');
                }
            }
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

    public function uploadfile($filePath, $object)
    {
        if ($this->ossClient) {
            try {
                return $this->ossClient->uploadFile($this->bucket, $object, $filePath, array());
            } catch (OssException $e) {
                writelog('oss:uploadFile:' . $e->getMessage());
                return;
            }
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

    public function downfile($object, $filePath)
    {
        if ($this->ossClient) {
            try {
                $this->ossClient->getObject($this->bucket, $object, array(OssClient::OSS_FILE_DOWNLOAD => $filePath));
            } catch (OssException $e) {
                writelog('oss:getObject:' . $e->getMessage());
            }
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

    public function signUrl($object)
    {
        if ($this->ossClient) {
            try {
                return $this->ossClient->signUrl($this->bucket, $object);
            } catch (OssException $e) {
                writelog('oss:getObject:' . $e->getMessage());
            }
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

    //删除文件和预览目录
    public function deletefile($object)
    {
        if ($this->ossClient) {
            try {
                $this->ossClient->deleteObject($this->bucket, $object);
            } catch (OssException $e) {
                writelog('oss:deletefile:' . $e->getMessage() . "\n");
            }

            try {
                $options = array(
                    'prefix' => GetDir($object),
                    'delimiter' => '/',
                    'max-keys' => 1000,
                    'marker' => '',
                );
                try {
                    $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
                } catch (OssException $e) {
                    writelog('oss:listObjects:' . $e->getMessage() . "\n");
                    return;
                }
                $objectList = $listObjectInfo->getObjectList();
                if (!empty($objectList)) {
                    foreach ($objectList as $objectInfo) {
                        $this->ossClient->deleteObject($this->bucket, $objectInfo->getKey());
                    }
                }
            } catch (OssException $e) {
                writelog('oss:deletedir:' . $e->getMessage() . "\n");
            }
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

    public function objectexists($object)
    {
        if ($this->ossClient) {
            try {
                return $this->ossClient->doesObjectExist($this->bucket, $object);
            } catch (OssException $e) {
                writelog('oss:deletefile:' . $e->getMessage() . "\n");
                return false;
            }
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

    public function get_pcount($path)
    {
        if ($this->ossClient) {
            $count = 0;
            try {
                $options = array(
                    'prefix' => $path,
                    'delimiter' => '/',
                    'max-keys' => 1000,
                    'marker' => '',
                );
                try {
                    $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
                } catch (OssException $e) {
                    writelog('oss:listObjects:' . $e->getMessage() . "\n");
                    return;
                }
                $objectList = $listObjectInfo->getObjectList();
                $count = count($objectList);
            } catch (OssException $e) {
                writelog('oss:deletedir:' . $e->getMessage() . "\n");
            }
            return $count;
        } else {
            writelog('oss:createBucket:ossClient is null');
        }
    }

}
