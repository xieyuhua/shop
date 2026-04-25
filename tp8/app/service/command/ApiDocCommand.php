<?php
declare(strict_types=1);

namespace app\service;

use think\console\Command;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\input\ArrayInput;
use think\console\Output;

class ApiDocCommand extends Command
{
    protected $signature = 'api:doc {action=generate : 生成API文档}';
    protected $description = 'API文档生成命令';

    protected function configure()
    {
        $this->configure = [
            'action' => Argument::OPTIONAL
        ];
    }

    protected function execute($input, $output)
    {
        $action = $input->getArgument('action');

        switch ($action) {
            case 'generate':
                $this->generate($output);
                break;
            case 'html':
                $this->generateHtml($output);
                break;
            default:
                $output->info('Usage: php think api:doc [generate|html]');
        }
    }

    protected function generate(Output $output)
    {
        $routes = include app()->getConfigPath() . 'route.php';
        
        $docs = [];
        foreach ($this->getRoutes() as $route) {
            $docs[] = [
                'method' => $route['method'],
                'path' => $route['path'],
                'controller' => $route['controller'],
                'action' => $route['action'],
            ];
        }

        $content = "<?php\n\nreturn " . var_export($docs, true) . ';';
        
        $file = app()->getRuntimePath() . 'api_doc.php';
        file_put_contents($file, $content);

        $output->info("API文档已生成: {$file}");
    }

    protected function generateHtml(Output $output)
    {
        $this->generate($output);
        
        $html = $this->buildHtmlDoc();
        
        $file = public_path() . 'api_doc.html';
        file_put_contents($file, $html);

        $output->info("HTML文档已生成: {$file}");
    }

    protected function getRoutes(): array
    {
        $routes = \think\facade\Route::getRules();
        
        $list = [];
        foreach ($routes as $route) {
            if (strpos($route->getRule()->getFullName(), 'api') !== false) {
                $list[] = [
                    'method' => implode(',', $route->getMethod()),
                    'path' => '/' . $route->getRule()->getFullName(),
                    'controller' => $route->getController(),
                    'action' => $route->getAction(),
                ];
            }
        }

        return $list;
    }

    protected function buildHtmlDoc(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>API接口文档</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .card { background: #fff; border-radius: 8px; padding: 20px; margin-bottom: 15px; }
        .method { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-right: 10px; }
        .GET { background: #61affe; color: #fff; }
        .POST { background: #49cc90; color: #fff; }
        .PUT { background: #fca130; color: #fff; }
        .DELETE { background: #f93e3e; color: #fff; }
        .path { font-size: 16px; color: #333; font-family: monospace; }
        .desc { color: #666; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>API接口文档</h1>
        <div class="card">
            <h2>认证</h2>
            <p>所有接口(除登录外)需要在Header中携带Token:</p>
            <code>Authorization: Bearer {token}</code>
        </div>
        <div id="content"></div>
    </div>
    <script>
        const apis = <?php echo json_encode(include app()->getRuntimePath() . 'api_doc.php', JSON_UNESCAPED_UNICODE); ?>;
        const methodColors = { GET: '#61affe', POST: '#49cc90', PUT: '#fca130', DELETE: '#f93e3e' };
        const html = apis.map(api => `
            <div class="card">
                <span class="method ${api.method}" style="background: ${methodColors[api.method]}">${api.method}</span>
                <span class="path">${api.path}</span>
                <div class="desc">${api.controller}::${api.action}</div>
            </div>
        `).join('');
        document.getElementById('content').innerHTML = html;
    </script>
</body>
</html>
HTML;
    }
}