<?php
declare(strict_types=1);


namespace Ekvio\Meta\Sdk\Common\Async;


use Ekvio\Meta\Sdk\ApiException;

class HttpFileResult implements Result
{
    private ?string $fileHost;

    public function __construct(?string $fileHost = null)
    {
        $this->fileHost = $fileHost;
    }

    public function get(string $url): string
    {
        if($this->fileHost) {
            $url = $this->modifyUrlHost($url);
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        $content = file_get_contents($url, false, $context);
        if($content === false) {
            ApiException::apiFailed(sprintf('Error in retrieve integration result by %s', $url));
        }

        return (string) $content;
    }

    private function modifyUrlHost(string $url): string
    {
        $url = parse_url($url);
        $path = $url['path'] ?? '';

        return sprintf('%s%s', $this->fileHost, $path);
    }
}