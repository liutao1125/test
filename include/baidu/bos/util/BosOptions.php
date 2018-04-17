<?php
/**
 * Created by PhpStorm.
 * User: cuican01
 * Date: 14-6-24
 * Time: 下午8:06
 */

namespace baidu\bce\bos\util;


class BosOptions {
    const ACCESS_KEY_ID = 'AccessKeyId';
    const ACCESS_KEY_SECRET = 'AccessKeySecret';
    const ENDPOINT = 'Endpoint';
    const CHARSET = 'Charset';

    const BUCKET = 'Bucket';
    const OBJECT = 'Object';

    const OBJECT_CONTENT_STRING = "ObjectContentString";
    const OBJECT_CONTENT_STREAM = "ObjectDataStream";

    const OBJECT_COPY_SOURCE = "CopySource";
    const OBJECT_COPY_SOURCE_IF_MATCH_TAG = "IfMatchTag";
    const OBJECT_COPY_METADATA_DIRECTIVE = "MetadataDirective";

    const BUCKET_LOCATION = "BucketLocation";

    const LIST_DELIMITER = "Delimiter";
    const LIST_MARKER = "Marker";
    const LIST_MAX_KEY_SIZE = "ListMaxKeySize";
    const LIST_PREFIX = "ListPrefix";
    const LIST_MAX_UPLOAD_SIZE =  "ListMaxUploadSize";

    const ACL = "Acl";

    const UPLOAD_ID = "UploadId";
    const PART_NUM = "PartNum";
    const PART_LIST = "PartList";

    const CONTENT_LENGTH = "Content-Length";
    const CONTENT_TYPE = "Content-Type";

    const MAX_PARTS_COUNT = "MaxPartsCount";
    const PART_NUMBER_MARKER = "PArtNumberMarker";
} 