import { ButtonGroup, Flex, IconButton } from "@chakra-ui/react";
import { LuChevronLeft, LuChevronRight } from "react-icons/lu";

type Props = {
  page: number;
  pageCount: number;
  onPageChange: (next: number) => void;
};

export function TicketPagination({ page, pageCount, onPageChange }: Props) {
  const pages = Array.from({ length: pageCount }, (_, i) => i + 1);

  return (
    <Flex justify="center" w="full" mt={6}>
      <ButtonGroup variant="ghost" size="md">
        <IconButton
          aria-label="Page précédente"
          disabled={page <= 1}
          color="red.800"
          borderWidth="1px"
          borderColor="red.800"
          _hover={{ bg: "red.800", color: "yellow.500" }}
          _disabled={{ opacity: 0.4, cursor: "not-allowed" }}
          onClick={() => onPageChange(Math.max(1, page - 1))}
        >
          <LuChevronLeft />
        </IconButton>

        {pages.map((p) => {
          const isSelected = p === page;
          return (
            <IconButton
              key={p}
              aria-label={`Aller à la page ${p}`}
              minW="40px"
              bg={isSelected ? "red.800" : "transparent"}
              color={isSelected ? "yellow.500" : "red.800"}
              borderWidth="1px"
              borderColor="red.800"
              _hover={{ bg: "red.800", color: "yellow.500" }}
              onClick={() => onPageChange(p)}
            >
              {p}
            </IconButton>
          );
        })}

        <IconButton
          aria-label="Page suivante"
          disabled={page >= pageCount}
          color="red.800"
          borderWidth="1px"
          borderColor="red.800"
          _hover={{ bg: "red.800", color: "yellow.500" }}
          _disabled={{ opacity: 0.4, cursor: "not-allowed" }}
          onClick={() => onPageChange(Math.min(pageCount, page + 1))}
        >
          <LuChevronRight />
        </IconButton>
      </ButtonGroup>
    </Flex>
  );
}
