<?php

namespace ProductBundle\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SelectedProductView implements \Serializable
{
    private const VIEW_LIST = 0;
    private const VIEW_GRID = 1;

    /** @var int */
    private $currentView;

    /**
     * @param SessionInterface $session
     * @return SelectedProductView
     */
    public static function getInstance(SessionInterface $session): self
    {
        $instance = $session->get('selectedProductView', null);

        if ($instance === null) {
            $instance = new self;

            $session->set('selectedProductView', $instance);
        }

        return $instance;
    }

    private function __construct()
    {
        $this->currentView = self::VIEW_LIST;
    }

    public function selectListView(): void
    {
        $this->currentView = self::VIEW_LIST;
    }

    public function selectGridView(): void
    {
        $this->currentView = self::VIEW_GRID;
    }

    /**
     * @return bool
     */
    public function getIsListView(): bool
    {
        return $this->currentView === self::VIEW_LIST;
    }

    /**
     * @return bool
     */
    public function getIsGridView(): bool
    {
        return $this->currentView === self::VIEW_GRID;
    }

    /**
     * @return string
     */
    public function serialize(): string
    {
        return serialize([
            $this->currentView,
        ]);
    }

    /**
     * @param  string $serialized
     */
    public function unserialize($serialized): void
    {
        [
            $this->currentView
        ] = unserialize($serialized, ['allowed_classes' => [self::class]]);
    }
}
