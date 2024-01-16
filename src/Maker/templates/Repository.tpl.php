<?php echo "<?php\n"; ?>

namespace App\Domain\<?php echo $domain; ?>\Repository;

use App\Domain\<?php echo $domain; ?>\Entity\<?php echo $entity; ?>;
use Doctrine\Persistence\ManagerRegistry;
use BackSystem\Base\Orm\AbstractRepository;

/**
 * @extends AbstractRepository<<?php echo $entity; ?>>
 */
class <?php echo $entity; ?>Repository extends AbstractRepository {

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, <?php echo $entity; ?>::class);
    }

}
