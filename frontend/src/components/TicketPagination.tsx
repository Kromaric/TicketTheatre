import { useState } from "react";
import { ButtonGroup, IconButton, Pagination } from "@chakra-ui/react";
import { LuChevronLeft, LuChevronRight } from "react-icons/lu";

export const TicketPagination = () => {
  const [page, setPage] = useState(1);

  return (
    <Pagination.Root
      count={20}
      pageSize={2}
      page={page}
      onPageChange={(event) => setPage(event.page)}
    >
      <ButtonGroup variant="ghost" size="md">
        <Pagination.PrevTrigger asChild>
          <IconButton aria-label="Page précédente" color="red.800">
            <LuChevronLeft />
          </IconButton>
        </Pagination.PrevTrigger>

        <Pagination.Items
          render={(pageItem) => {
            const isSelected = pageItem.value === page;

            return (
              <IconButton
                key={pageItem.value}
                aria-label={`Aller à la page ${pageItem.value}`}
                bg={isSelected ? "red.800" : "transparent"}
                color={isSelected ? "yellow.500" : "red.800"}
                borderWidth="1px"
                borderColor="red.800"
                _hover={{
                  bg: "red.800",
                  color: "yellow.500",
                }}
              >
                {pageItem.value}
              </IconButton>
            );
          }}
        />

        <Pagination.NextTrigger asChild>
          <IconButton aria-label="Page suivante" color="red.800">
            <LuChevronRight />
          </IconButton>
        </Pagination.NextTrigger>
      </ButtonGroup>
    </Pagination.Root>
  );
};
