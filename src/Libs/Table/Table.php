<?php

namespace Zephyrforge\Zephyrforge\Libs\Table;

use krzysztofzylka\DatabaseManager\Condition;
use Krzysztofzylka\Request\Request;
use Throwable;
use Zephyrforge\Zephyrforge\Exception\HiddenException;
use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;
use Zephyrforge\Zephyrforge\Model;
use Zephyrforge\Zephyrforge\View;

class Table
{

    /**
     * Conditions
     * @var array
     */
    public array $conditions = [];

    /**
     * Table id
     * @var string|null
     */
    protected ?string $id = null;

    /**
     * Table actions
     * @var ?array
     */
    protected ?array $actions = null;

    /**
     * Columns
     * @var array
     */
    protected array $columns = [];

    /**
     * Table data
     * @var array
     */
    protected array $data = [];

    /**
     * Page limit
     * @var int
     */
    protected int $pageLimit = 10;

    /**
     * Actual page
     * @var int
     */
    protected int $page = 1;

    /**
     * Pages
     * @var ?int
     */
    protected ?int $pages = null;

    /**
     * Is ajax action
     * @var bool
     */
    protected bool $isAjaxAction = false;

    /**
     * Search data
     * @var string|null
     */
    protected ?string $search = null;

    /**
     * Slim table
     * @var bool
     */
    protected bool $slim = true;

    /**
     * Model
     * @var Model|null
     */
    protected ?Model $model = null;

    /**
     * order by
     * @var ?string
     */
    protected ?string $orderBy = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $data = TableReminder::getData($this);

        if (isset($data['page'])) {
            $this->page = $data['page'];
        }

        if (isset($data['search'])) {
            $this->search = $data['search'];
        }
    }

    /**
     * Render table
     * @return string
     * @throws HiddenException
     * @throws Throwable
     * @throws MainException
     * @throws NotFoundException
     */
    public function render(): string
    {
        $this->ajaxAction();

        $footerInfoOf = $this->getDataCount();

        if ($this->getPage() === $this->getPages()) {
            $footerInfoTo = $footerInfoOf;
        } else {
            $footerInfoTo = $this->getPage() * $this->getPageLimit();
        }

        $footerInfoFrom = $footerInfoTo === 0 ? 0 : (($this->getPage() - 1) * $this->getPageLimit() + 1);

        $variables = [
            'id' => $this->getId(),
            'here' => $_SERVER['REQUEST_URI'],
            'header' => [
                'actions' => $this->getActions(),
                'search' => [
                    'action' => RenderAction::generate($this, 'search'),
                    'value' => $this->getSearch()
                ]
            ],
            'table' => [
                'columns' => $this->getRenderColumn(),
                'slim' => $this->isSlim(),
                'data' => $this->getRenderData()
            ],
            'footer' => [
                'info' => [
                    'from' => $footerInfoFrom,
                    'to' => $footerInfoTo,
                    'of' => $footerInfoOf
                ],
                'pagination' => $this->prepareRenderPaginationData()
            ]
        ];

        $view = new View();

        ob_start();
        $view->render(__DIR__ . '/View/table.twig', $variables);
        $content = ob_get_clean();

        if ($this->isAjaxAction) {
            ob_clean();
            echo $content;
            exit;
        }

        return $content;
    }

    /**
     * Get data
     * @param bool $full
     * @return array
     * @throws Throwable
     * @throws HiddenException
     */
    public function getData(bool $full = true): array
    {
        if (!is_null($this->search)) {
            $this->setData($this->search($this->data, $this->search));
        }

        if (!is_null($this->getModel())) {
            $this->setData($this->getModel()->readAll(
                $this->getConditions(),
                null,
                $this->getOrderBy(),
                $this->generateLimit()
            ));
        } elseif (!$full) {
            return array_slice($this->data, (($this->page - 1) * $this->pageLimit), $this->pageLimit);
        }

        return $this->data;
    }

    /**
     * Set data
     * @param array $data
     * @return void
     * @throws HiddenException
     */
    public function setData(array $data): void
    {
        $this->data = $data;

        $this->setPages(ceil($this->getDataCount() / $this->pageLimit));
    }

    /**
     * Get data count
     * @return int
     * @throws HiddenException
     */
    public function getDataCount(): int
    {
        if (!is_null($this->getModel())) {
            return $this->getModel()->count($this->getConditions());
        }

        return count($this->data);
    }

    /**
     * Get order by
     * @return string|null
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * Set order by
     * @param string|null $orderBy
     * @return void
     */
    public function setOrderBy(?string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    /**
     * Get slim
     * @return bool
     */
    public function isSlim(): bool
    {
        return $this->slim;
    }

    /**
     * Set slim
     * @param bool $slim
     * @return void
     */
    public function setSlim(bool $slim): void
    {
        $this->slim = $slim;
    }

    /**
     * Get columns
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Add column
     * @param string $key
     * @param string $name
     * @param callable|string|null $value
     * @param array $attributes
     * @return void
     */
    public function addColumn(
        string $key,
        string $name,
        null|callable|string $value = null,
        array $attributes = []
    ): void
    {
        $this->columns[$key] = [
            'name' => $name,
            'attributes' => $attributes,
            'value' => $value
        ];
    }

    /**
     * Remove column
     * @param string $key
     * @return void
     */
    public function removeColumn(string $key): void
    {
        unset($this->columns[$key]);
    }

    /**
     * Add condition
     * @param Condition $condition
     * @return void
     */
    public function addCondition(Condition $condition): void
    {
        $this->conditions[] = $condition;
    }

    /**
     * Get conditions
     * @return array
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * Get actions
     * @return ?array
     */
    public function getActions(): ?array
    {
        return $this->actions;
    }

    /**
     * Add actions
     * @param string $name
     * @param string $href
     * @param ?string $class
     * @return void
     */
    public function addAction(string $name, string $href, ?string $class = ''): void
    {
        $this->actions[$href] = [
            'name' => $name,
            'class' => $class
        ];
    }

    /**
     * Remove action
     * @param string $html
     * @return void
     */
    public function removeAction(string $html): void
    {
        unset($this->actions[$html]);
    }

    /**
     * Get table ID
     * @return string The object's ID.
     */
    public function getId(): string
    {
        if (is_null($this->id)) {
            $this->setId('table');

        }

        return $this->id;
    }

    /**
     * Set table ID
     * @param string|null $id The ID to set for the object. Set to null if the automatic generate ID.
     * @return void
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Get page limit
     * @return int
     */
    public function getPageLimit(): int
    {
        return $this->pageLimit;
    }

    /**
     * Set page limit
     * @param int $pageLimit
     * @return void
     */
    public function setPageLimit(int $pageLimit): void
    {
        $this->pageLimit = $pageLimit;
    }

    /**
     * Get pages count
     * @return int
     * @throws HiddenException
     */
    public function getPages(): int
    {
        if (is_null($this->pages)) {
            $this->setPages(ceil($this->getDataCount() / $this->pageLimit));
        }

        return $this->pages ?? 1;
    }

    /**
     * Set pages count
     * @param int $pages
     * @return void
     * @throws HiddenException
     */
    public function setPages(int $pages): void
    {
        $this->pages = max($pages, 1);

        if ($this->getPage() > $this->pages) {
            $this->setPage($this->pages);
        }
    }

    /**
     * Get actual page
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Set actual page
     * @param int $page
     * @return void
     * @throws HiddenException
     */
    public function setPage(int $page): void
    {
        if ($page > $this->getPages()) {
            $page = $this->getPages();
        }

        $this->page = max($page, 1);

        TableReminder::saveData($this, ['page' => $this->page]);
    }

    /**
     * Get search
     * @return string|null
     */
    public function getSearch(): ?string
    {
        return $this->search;
    }

    /**
     * Set search
     * @param string|null $search
     * @return void
     */
    public function setSearch(?string $search): void
    {
        if (!is_null($search) && empty($search)) {
            $search = null;
        }

        $this->search = $search;

        TableReminder::saveData($this, ['search' => $this->search]);
    }

    /**
     * Get model
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * Set model
     * @param Model|null $model
     * @return void
     */
    public function setModel(?Model $model): void
    {
        $this->model = $model;
    }

    /**
     * Generate limit
     * @return string
     */
    protected function generateLimit(): string
    {
        return (($this->getPage() - 1) * $this->getPageLimit()) . ', ' . $this->getPageLimit();
    }

    /**
     * Ajax actions
     * @return void
     * @throws HiddenException
     */
    protected function ajaxAction(): void
    {
        if (!Request::isPost() || !Request::isAjaxRequest() || Request::getPostData('layout') !== 'table') {
            return;
        }

        $this->isAjaxAction = true;
        $params = Request::getPostData('params');

        switch (Request::getPostData('action')) {
            case 'pagination':
                $this->setPage($params['page']);
                break;
            case 'search':
                $this->setSearch($params['table-search']);
                break;
        }
    }

    /**
     * Search method
     * @param array $data
     * @param string $search
     * @return array
     * @throws Throwable
     */
    protected function search(array $data, string $search): array
    {
        if (!is_null($this->getModel())) {
            foreach (array_keys($this->getColumns()) as $columnKey) {
                if (str_contains($columnKey, '.')) {
                    $this->conditions['OR'][] = new Condition($columnKey, 'LIKE', '%' . htmlspecialchars($this->getSearch()) . '%');
                }
            }

            return [];
        }

        $matches = [];
        $regex = '/' . preg_quote($search, '/') . '/i';

        foreach ($data as $row) {
            foreach ($row as $value) {
                try {
                    if (is_string($value) && preg_match($regex, $value)) {
                        $matches[] = $row;

                        break;
                    }
                } catch (Throwable $throwable) {
                    Log::throwableLog($throwable);

                    throw $throwable;
                }
            }
        }

        return $matches;
    }

    /**
     * Get prepared render data
     * @return array
     * @throws HiddenException
     * @throws Throwable
     */
    protected function getRenderData(): array
    {
        $data = $this->getData(false);

        foreach ($data as $dataKey => $dataValue) {
            foreach ($dataValue as $columnName => $columnValue) {
                if (isset($this->getColumns()[$columnName]) && $this->getColumns()[$columnName]['value']) {
                    $columnValueVariable = $this->getColumns()[$columnName]['value'];

                    if (is_string($columnValueVariable) || is_numeric($columnValueVariable) || is_int($columnValueVariable)) {
                        $columnValue = $columnValueVariable;
                    } elseif (!is_null($this->getModel()) && is_null($columnValueVariable)) {
                        try {
                            $generatedArray = '["' . implode('"]["', explode('.', $columnName)) . '"]';
                            $columnValue = @eval('return $data' . $generatedArray . ';');
                        } catch (\Throwable) {
                        }
                    }

                    if (is_callable($columnValueVariable)) {
                        $cell = new Cell();
                        $cell->value = $columnValue;
                        $cell->data = $dataValue;

                        $columnValue = $columnValueVariable($cell);
                    }
                }

                $data[$dataKey][$columnName] = $columnValue;
            }
        }

        return $data;
    }

    /**
     * Prepare column data for render
     * @return array
     */
    protected function getRenderColumn(): array
    {
        $columns = $this->getColumns();

        foreach ($columns as $key => $column) {
            if (isset($column['attributes']) && is_array($column['attributes'])) {
                $columns[$key]['attributes'] = $this->generateAttributes($column['attributes']);
            }
        }

        return $columns;
    }

    /**
     * Prepare pagination data for render
     * @throws HiddenException
     */
    protected function prepareRenderPaginationData(): array
    {
        $data = [];
        $previousDisabled = false;
        $nextDisabled = false;
        $currentPage = $this->getPage();
        $totalPages = $this->getPages();

        if ($currentPage === 1) {
            $previousDisabled = true;
        }

        if ($currentPage === $totalPages) {
            $nextDisabled = true;
        }

        $data[] = [
            'value' => '<<',
            'disabled' => $previousDisabled,
            'page' => 1,
            'action' => RenderAction::generate($this, 'pagination', ['page' => 1])
        ];

        $data[] = [
            'value' => '<',
            'disabled' => $previousDisabled,
            'page' => $currentPage - 1,
            'action' => RenderAction::generate($this, 'pagination', ['page' => $currentPage - 1])
        ];

        $start = ($currentPage - 3 > 0) ? $currentPage - 3 : 1;
        $end = ($currentPage + 3 <= $totalPages) ? $currentPage + 3 : $totalPages;

        for ($page = $start; $page <= $end; $page++) {
            $data[] = [
                'value' => $page,
                'disabled' => $page === $currentPage,
                'page' => $page,
                'action' => RenderAction::generate($this, 'pagination', ['page' => $page])
            ];
        }

        $data[] = [
            'value' => '>',
            'disabled' => $nextDisabled,
            'page' => $currentPage + 1,
            'action' => RenderAction::generate($this, 'pagination', ['page' => $currentPage + 1])
        ];

        $data[] = [
            'value' => '>>',
            'disabled' => $nextDisabled,
            'page' => $totalPages,
            'action' => RenderAction::generate($this, 'pagination', ['page' => $totalPages])
        ];

        return $data;
    }

    /**
     * Generate HTML attribute string.
     * This method iterates over the attributes array and generates the corresponding HTML attribute string.
     * @param array $attributes
     * @return string The generated HTML attribute string.
     */
    protected function generateAttributes(array $attributes = []): string
    {
        $attributesHtml = '';

        foreach ($attributes as $key => $value) {
            if (str_contains($value, '"')) {
                $attributesHtml .= ' ' . $key . '=\'' . $value . '\'';
            } else {
                $attributesHtml .= ' ' . $key . '="' . $value . '"';
            }
        }


        return $attributesHtml;
    }


}