<?php


namespace Arbory\Base\Admin\Layout;


use Arbory\Base\Admin\Form;
use Arbory\Base\Admin\Form\Fields\FieldInterface;
use Arbory\Base\Admin\Form\FieldSet;
use Arbory\Base\Admin\Layout\Transformers\AppendTransformer;
use Arbory\Base\Admin\Layout\Transformers\WrapTransformer;
use Arbory\Base\Admin\Panels\FieldSetPanel;
use Arbory\Base\Admin\Panels\Renderer;
use Arbory\Base\Admin\Panels\Panel;
use Arbory\Base\Html\Elements\Content;
use Closure;

class PanelLayout extends AbstractLayout implements LayoutInterface
{
    protected $panels = [];

    /**
     * @var \SplObjectStorage
     */
    protected $fields;

    /**
     * @var Form
     */
    protected $form;

    public function __construct()
    {
        $this->fields = new \SplObjectStorage();
    }

    public function setPanels(Closure $closure)
    {
        $this->panels = $closure($this);
    }

    public function panel($name, callable $closure)
    {
        $panel = new FieldSetPanel();

        $fields = $closure(new FieldSet($this->form->getModel(), $this->form->fields()->getNamespace()), $panel);

        $panel->setTitle($name);
        $panel->setFields($fields);

        /**
         * @var FieldInterface[] $fields
         */
        foreach($fields as $field)
        {
            $this->fields->attach($field, [
                'panel' => $panel
            ]);

            $this->form->fields()->add($field);
        }

        $this->panels[] = $panel;

        return $panel;
    }

    public function contents($content)
    {
        return new Content([
            $content
        ]);
    }

    protected function sidebar()
    {
        $panel = new Panel();

        $panel->setTitle('Sidebar panel');
        $panel->setContents('Content here');

        return (new Renderer())->render($panel);
    }

    function build()
    {
        $this->use(new WrapTransformer(new Form\Builder($this->form)));
        $this->use((new Form\Layout())->setForm($this->form));
        $this->use(new SidebarLayout($this->sidebar()));

        $this->setContent($this->renderPanels());

//        $this->use(new AppendTransformer($this->renderPanels()));
    }

    public function renderPanels()
    {
        $contents = new Content();

        foreach($this->panels as $panel)
        {
            $contents->push((new Renderer())->render($panel));
        }

        return $contents;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return PanelLayout
     */
    public function setForm(Form $form): self
    {
        $this->form = $form;

        return $this;
    }
}