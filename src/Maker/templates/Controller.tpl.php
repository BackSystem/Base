<?php echo "<?php\n"; ?>

namespace App\Http\<?php echo $layer; ?>\Controller;

use BackSystem\Base\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class <?php echo $controller; ?> extends AbstractController {

    #[Route('/<?php echo $route_url; ?>', name: '<?php echo $route_name; ?>', methods: ['GET'])]
    public function index(): Response {
        return $this->render('<?php echo $template_path; ?>');
    }

}
