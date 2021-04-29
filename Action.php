<?php

class JoePlugin_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public function __construct($request, $response, $params)
    {
        parent::__construct($request, $response, $params);
        $this->res = new Typecho_Response();
        call_user_func(array(
            $this,
            $this->request->type
        ));
    }

    private function search()
    {
        $db = Typecho_Db::get();
        $limit = Helper::options()->plugin('JoePlugin')->JSearchNum;
        if ($limit && $limit !== 'off') {
            $options = Helper::options();
            $keyword = self::GET('keyword', '');
            $searchQuery = '%' . str_replace(' ', '%', $keyword) . '%';
            $articles = $db->fetchAll(
                $db->select()->from('table.contents')
                    ->where('table.contents.title LIKE ? OR table.contents.text LIKE ?', $searchQuery, $searchQuery)
                    ->where("table.contents.password IS NULL")
                    ->where('table.contents.status = ?', 'publish')
                    ->where('table.contents.created < ?', $options->gmtTime)
                    ->where('table.contents.type = ?', 'post')
                    ->limit($limit)
                    ->order('table.contents.created', Typecho_Db::SORT_DESC)
            );
            if (count($articles) > 0) {
                foreach ($articles as $article) {
                    $type = $article['type'];
                    $routeExists = (NULL != Typecho_Router::get($type));
                    $article['pathinfo'] = $routeExists ? Typecho_Router::url($type, $article) : '#';
                    $article['permalink'] = Typecho_Common::url($article['pathinfo'], $options->index);
                    echo "标题：{$article['title']}\n{$article['permalink']}\n";
                }
            } else {
                echo "没有搜索到相关文章！";
            }
        }
    }

    public function GET($key, $default = '')
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    public function action()
    {
        $this->on($this->request);
    }
}
